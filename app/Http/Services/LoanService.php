<?php

namespace App\Http\Services;

use App\Enums\LoanPaymentStatus;
use App\Enums\LoanTenureType;
use App\Enums\RepaymentScheduleStatus;
use App\Enums\UserType;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\RepaymentSchedule;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Support\Facades\DB;

class LoanService
{

    public function createLoan($loan_input, User $user)
    {
        $customer = $this->findCustomerByUserId($user->id);

        if (!$customer) {
            throw new Error('Customer infomation missing');
        }

        $loan_input = json_decode($loan_input);
        $loan = Loan::create([
            'customer_id' => $customer->id,
            'tenure' => $loan_input->term,
            'tenure_type' => LoanTenureType::WEEKLY->value,
            'currency' => $loan_input->currency,
            'amount' => $loan_input->amount,
            'status' => LoanPaymentStatus::PENDING->value,
        ]);

        $loan->refresh();

        $data = [
            'loan_reference_number' => $loan->loan_reference_number,
            'tenure' => $loan->tenure,
            'tenure_type' => $loan->tenure_type,
            'currency' => $loan->currency,
            'amount' => $loan->amount,
            'status' => $loan->status,
        ];

        return ([
            'loan' => $data
        ]);
    }

    public function approveLoan(string $loan_number, User $user)
    {
        try {

            // Checks if the loan is present or not
            $loan = Loan::where('loan_reference_number', $loan_number)
                ->whereNull('loan.deleted_at')
                ->firstOrFail();

            if ($loan->status === LoanPaymentStatus::APPROVED->value) {
                throw new Error('Loan ' . $loan_number . ' is already approved');
            }

            /*
                Mark the loan as approved
                and then generate its payment schedules within
                a single transaction
            */

            DB::beginTransaction();

            $updatedLoan = $this->updateLoanAsApproved($loan, $user);
            $this->calculateRepaymentSchedules($updatedLoan);

            DB::commit();

            return 'Approved successfully';
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectLoan(string $loan_number, string $reject_reason, User $user)
    {
        try {
            $loan = Loan::where('loan_reference_number', $loan_number)
                ->where('loan.status', LoanPaymentStatus::PENDING->value)
                ->whereNull('loan.deleted_at')
                ->first();

            if (!$loan) {
                throw new Error('No pending loan found to reject');
            }

            $loan->status = LoanPaymentStatus::REJECTED->value;
            $loan->rejected_by = $user->id;
            $loan->reject_reason = $reject_reason;
            $loan->save();
            $loan->refresh();

            return 'Rejected successfully';
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLoansByCustomer($user_id)
    {
        try {
            $loan_details = Loan::join('customer', 'loan.customer_id', '=', 'customer.id')
                ->select([
                    'loan.loan_reference_number',
                    'loan.tenure',
                    'loan.tenure_type',
                    'loan.currency',
                    'loan.amount',
                    'loan.status',
                ])
                ->where('customer.user_id', $user_id)
                ->whereNull('loan.deleted_at')
                ->whereNull('customer.deleted_at')
                ->orderBy('loan.updated_at', 'desc')
                ->get(['loan.*']);

            return ([
                'loans' => $loan_details
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLoansByAdmin($status)
    {
        try {
            $loan_details = Loan::when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')->get();

            return ([
                'loans' => $loan_details
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processRepayment($repayment_details, $user)
    {
        $repayment_details = json_decode($repayment_details);
        try {
            $loan_details = $this->getLoanByReferenceNumber($repayment_details->loan_number, $user->id);

            if (!$loan_details) {
                throw new Error('Error in processing payment. Loan details not found');
            }

            if ($loan_details->status === LoanPaymentStatus::PAID->value) {
                throw new Error('Loan ' . $repayment_details->loan_number . ' is already paid');
            }

            if ($loan_details->status !== LoanPaymentStatus::APPROVED->value) {
                throw new Error('You cannot pay for an unapproved loan');
            }

            /*
                Find the pending payments related to 
                the particular loan by searching 
                the repayment_schedule table
            */
            $pending_payments = $this->findPendingPayments($loan_details->id);


            // If no payments pending
            if (count($pending_payments) < 1) {
                throw new Error('No payments pending');
            }

            // Start the process of accounting the amount remitted by user
            $this->accountLoanRepayment($loan_details, $repayment_details, $pending_payments);

            return 'Payment accounted successfully';
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLoanById($loan_id, $logged_user)
    {
        $loan_details = Loan::join('customer', 'loan.customer_id', '=', 'customer.id')
            ->join('user', 'user.id', '=', 'customer.user_id')
            ->select([
                'customer.id as customer_id',
                'user.full_name as full_name',
                'user.email as email',
                'user.phone_number as phone_number',
                'user.profile_picture as profile_picture',
                'customer.address as address',
                'loan.loan_reference_number as loan_number',
                'loan.tenure as tenure',
                'loan.tenure_type as tenure_type',
                'loan.currency as currency',
                'loan.amount as amount',
                'loan.status as status',
                'loan.created_at as created_at',
                'loan.updated_at as updated_at',
            ])
            ->where('loan.id', $loan_id)
            ->when($logged_user, function ($query) use ($logged_user) {
                if ($logged_user->type === UserType::CUSTOMER->value) {
                    return $query->where('customer.user_id', $logged_user->id);
                }
            })
            ->whereNull('loan.deleted_at')
            ->whereNull('customer.deleted_at')
            ->orderBy('loan.updated_at', 'desc')
            ->get();

        return ([
            'loan' => $loan_details
        ]);
    }

    public function viewRepaymentSchedulesOfALoan($loan_number, $logged_user)
    {

        $loan = Loan::join('customer', 'loan.customer_id', '=', 'customer.id')
            ->join('user', 'user.id', '=', 'customer.user_id')
            ->select([
                'loan.id as id',
                'loan.loan_reference_number as loan_number',
                'loan.tenure as tenure',
                'loan.tenure_type as tenure_type',
                'loan.currency as currency',
                'loan.amount as amount',
                'loan.status as status',
                'loan.created_at as created_at',
                'loan.updated_at as updated_at',
            ])
            ->when($logged_user, function ($query) use ($logged_user) {
                if ($logged_user->type === UserType::CUSTOMER->value) {
                    return $query->where('customer.user_id', $logged_user->id);
                }
            })
            ->where('loan.loan_reference_number', $loan_number)
            ->whereNull('loan.deleted_at')
            ->first();

        if (!$loan || $loan->status === 'PENDING' || $loan->status === 'REJECTED') {
            throw new Error('Loan not found');
        }

        $repayment_schedules = RepaymentSchedule::where('loan_id', $loan->id)
            ->whereNull('deleted_at')
            ->orderBy('due_date', 'asc')
            ->get();

        return ([
            'loan' => $loan,
            'repayment_schedules' => $repayment_schedules
        ]);
    }

    private function updateLoanAsApproved(Loan $loan, User $user)
    {
        $loan->status = LoanPaymentStatus::APPROVED->value;
        $loan->approved_by = $user->id;
        $loan->save();
        $loan->refresh();

        return $loan;
    }

    private function calculateRepaymentSchedules(Loan $loan_details)
    {
        $total_amount = $loan_details->amount;
        $total_term = $loan_details->tenure;
        $installment = ($total_amount) / ($total_term);

        $repayment_schedules = [];

        for ($each_term = 1; $each_term <= $total_term; $each_term++) {
            $schedule = $this->generateRepaymentSchedule($loan_details->id, $each_term, $installment);
            $repayment_schedules[] = $schedule;
        }

        return $repayment_schedules;
    }

    private function generateRepaymentSchedule($loan_id, $term, $installment)
    {
        $due_date = date('Y-m-d H:i:s', strtotime('+' . $term . 'weeks'));

        $repayment_schedule = RepaymentSchedule::create([
            'loan_id' => $loan_id,
            'amount' => $installment,
            'due_date' => $due_date,
            'status' => RepaymentScheduleStatus::PENDING->value,
        ]);

        $repayment_schedule->refresh();

        return $repayment_schedule;
    }

    private function getLoanByReferenceNumber($loan_number, $user_id)
    {
        return Loan::join('customer', 'loan.customer_id', '=', 'customer.id')
            ->join('user', 'customer.user_id', '=', 'user.id')
            ->select([
                'loan.id as id',
                'loan.loan_reference_number as loan_number',
                'loan.tenure as tenure',
                'loan.tenure_type as tenure_type',
                'loan.currency as currency',
                'loan.amount as amount',
                'loan.status as status',
                'loan.created_at as created_at',
                'loan.updated_at as updated_at',
            ])
            ->where('user.id', $user_id)
            ->where('loan.loan_reference_number', $loan_number)
            ->whereNull('loan.deleted_at')
            ->whereNull('customer.deleted_at')
            ->first();
    }

    private function findPendingPayments($loan_id)
    {
        return RepaymentSchedule::where('repayment_schedule.loan_id', $loan_id)
            ->where('repayment_schedule.status', RepaymentScheduleStatus::PENDING->value)
            ->whereNull('repayment_schedule.deleted_at')
            ->orderBy('repayment_schedule.due_date', 'asc')
            ->get();
    }

    private function findPaidPayments($loan_id)
    {
        return RepaymentSchedule::where('repayment_schedule.loan_id', $loan_id)
            ->where('repayment_schedule.status', RepaymentScheduleStatus::PAID->value)
            ->whereNull('repayment_schedule.deleted_at')
            ->orderBy('repayment_schedule.due_date', 'asc')
            ->get();
    }

    private function accountLoanRepayment($loan_details, $repayment_details, $pending_payments)
    {
        try {

            DB::beginTransaction();

            $pending_payment_terms = count($pending_payments);
            $remitting_amount = $repayment_details->amount;

            // If the remitting amount is higher than the total amount taken as loan
            if ($remitting_amount > $loan_details->amount) {
                throw new Error('The amount trying to remit is higher than the loaned principal amount');
            }

            $due_payment_details = $pending_payments->first();

            /* 
                TODO: A loan statement table can be introduced here
                which can make a record of the different CREDIT/DEBIT
                transactions that is taking place as related to this loan entity.

                But since currently this is just a representation of things and 
                there is only one simple way of single dispersal, it is not necessary. 
                But in real world systems it is very highly recommended
            
            */


            //If the remitting amount is lower than the due installment amount
            if ($remitting_amount < $due_payment_details->amount) {

                throw new Error('Insufficient amount. Due amount is ' . $loan_details->currency . ' ' . $due_payment_details->amount);
            
            } elseif ($remitting_amount == $due_payment_details->amount) {
                
                /*
                    If the remitting amount is same as due amount. Simply mark
                    the payment schedule as paid 
                */

                $this->updatePaymentScheduleAsPaid($loan_details->id, $due_payment_details->id, $pending_payment_terms, $remitting_amount);
            
            } elseif ($remitting_amount > $due_payment_details->amount) {
             

                // If this is the last payable schedule and excess amount is remitted. We will not account the payment
                if ($pending_payment_terms == 1) {
                    throw new Error('Payment exceeds the due amount. Due amount is ' . $loan_details->currency . ' ' . $due_payment_details->amount);
                }


                /*
                    If the remitting amount is more than the due amount.
                    Then we have to accept the remitting amount and do 
                    a recalculation of the remaining loan amount and its
                    corresponding installment amounts
                */

                $this->revisitPaymentSchedule($loan_details, $remitting_amount, $pending_payment_terms, $due_payment_details->id);
                $this->updatePaymentScheduleAsPaid($loan_details->id, $due_payment_details->id, $pending_payment_terms, $remitting_amount);
            
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Error('Failed to account the repayment, Error ' . $e);
        }
    }

    private function revisitPaymentSchedule($loan_details, $remitting_amount, $pending_payment_terms, $due_payment_id)
    {
        $paid_terms = $this->findPaidPayments($loan_details->id);
        $total_paid_amount = 0;
        foreach ($paid_terms as $term) {
            $total_paid_amount += $term->amount;
        }
        if (($remitting_amount + $total_paid_amount) > $loan_details->amount) {
            throw new Error('Total remittance exceeds the loaned amount. Please try a smaller amount');
        }

        $remaining_total_principal_amount = $loan_details->amount - ($remitting_amount + $total_paid_amount);
        $remaining_payment_terms = $pending_payment_terms - 1;

        $revised_installment = $remaining_total_principal_amount / $remaining_payment_terms;

        $this->revisePendingPaymentSchedules($loan_details->id, $due_payment_id, $revised_installment);
    }

    private function updatePaymentScheduleAsPaid($loan_id, $repayment_schedule_id, $pending_payment_terms, $remitting_amount)
    {
        try {
            DB::beginTransaction();
            $updated_payment_schedule = RepaymentSchedule::where('repayment_schedule.id', $repayment_schedule_id)
                ->whereNull('repayment_schedule.deleted_at')
                ->update([
                    'amount' => $remitting_amount,
                    'status' => RepaymentScheduleStatus::PAID->value,
                ]);

            if ($pending_payment_terms === 1) {

                // If its the last installment. Mark loan as PAID too.
                $this->updateLoanAsPaid($loan_id);
            }

            DB::commit();

            return $updated_payment_schedule;
        } catch (Exception $e) {

            DB::rollBack();
            throw new Error('Failed to update payment status, Please try again later');
        }
    }

    private function revisePendingPaymentSchedules($loan_id, $current_due_schedule_id, $revised_installment)
    {
        try {
            DB::beginTransaction();

            /*
                After recalculation, if the future payment schedules are already covered in the
                current payments. We can mark the future schedules as WAIVED. So that they will
                be skipped and loan will be closed as PAID
            */
            if ($revised_installment == 0) {
                RepaymentSchedule::where('repayment_schedule.status', RepaymentScheduleStatus::PENDING->value)
                    ->where('repayment_schedule.id', '!=', $current_due_schedule_id)
                    ->update([
                        'status' => RepaymentScheduleStatus::WAIVED->value,
                        'deleted_at' => now(),
                    ]);
                $this->updateLoanAsPaid($loan_id);
            } else {
                RepaymentSchedule::where('repayment_schedule.status', RepaymentScheduleStatus::PENDING->value)
                    ->where('repayment_schedule.id', '!=', $current_due_schedule_id)
                    ->whereNull('repayment_schedule.deleted_at')
                    ->update([
                        'amount' => $revised_installment,
                    ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Error('Error in updating payment schedules. Please try again later');
        }
    }

    private function updateLoanAsPaid($loan_id)
    {
        return Loan::where('loan.id', $loan_id)
            ->whereNull('loan.deleted_at')
            ->update([
                'status' => LoanPaymentStatus::PAID->value,
            ]);
    }

    private function findCustomerByUserId($user_id)
    {
        return Customer::where('customer.user_id', $user_id)
            ->whereNull('customer.deleted_at')
            ->first();
    }
}
