<?php

namespace App\Http\Services;

use App\Models\Loan;
use App\Models\RepaymentSchedule;
use Error;
use Exception;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function createLoan($loan_input)
    {
        $loan_input = json_decode($loan_input);
        $loan = Loan::create([
            'customer_id' => '0316453b-58ff-44fd-8c71-9d54343575c6',
            'tenure' => $loan_input->term,
            'tenure_type' => 'WEEKLY',
            'currency' => $loan_input->currency,
            'amount' => $loan_input->amount,
            'status' => 'PENDING',
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

        return $data;
    }

    public function approveLoan(string $loan_number)
    {
        try {
            $loan = $this->getLoanByReferenceNumber($loan_number);

            if ($loan->status == 'APPROVED') {
                throw new Error('Loan ' . $loan_number . ' is already approved');
            }

            DB::beginTransaction();

            $updatedLoan = $this->updateLoanAsApproved($loan);
            $this->calculateRepaymentSchedules($updatedLoan);

            DB::commit();

            return 'Approved successfully';
        } catch (Exception $e) {
            DB::rollBack();
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

            return $loan_details;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processRepayment($repayment_details)
    {
        //loan_number
        //amount
        //currency
        $repayment_details = json_decode($repayment_details);
        try {
            $loan_details = $this->getLoanByReferenceNumber($repayment_details->loan_number);

            if ($loan_details->status == 'PAID') {
                throw new Error('Loan ' . $repayment_details->loan_number . ' is already paid');
            }

            if ($loan_details->status !== 'APPROVED') {
                throw new Error('You cannot pay for an unapproved loan');
            }

            $pending_payments = $this->findPendingPayments($loan_details->id);

            if (count($pending_payments) < 1) {
                throw new Error('No payments pending');
            }

            $this->accountRepayment($loan_details, $repayment_details, $pending_payments);

            return 'Payment accounted successfully';
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function updateLoanAsApproved(Loan $loan)
    {
        $loan->status = 'APPROVED';
        $loan->approved_by = '6b6205bd-60ed-49e8-be36-9cbc61b24ea7';
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
            'status' => 'PENDING',
        ]);

        $repayment_schedule->refresh();

        return $repayment_schedule;
    }

    private function getLoanByReferenceNumber($loan_number)
    {
        return Loan::where('loan_reference_number', $loan_number)->firstOrFail();
    }

    private function findPendingPayments($loan_id)
    {
        return RepaymentSchedule::where('repayment_schedule.loan_id', $loan_id)
            ->where('repayment_schedule.status', 'PENDING')
            ->orderBy('repayment_schedule.due_date', 'asc')
            ->get();
    }

    private function findPaidPayments($loan_id)
    {
        return RepaymentSchedule::where('repayment_schedule.loan_id', $loan_id)
            ->where('repayment_schedule.status', 'PAID')
            ->orderBy('repayment_schedule.due_date', 'asc')
            ->get();
    }

    private function accountRepayment($loan_details, $repayment_details, $pending_payments)
    {
        try {
            DB::beginTransaction();

            $pending_payment_terms = count($pending_payments);
            $remitting_amount = $repayment_details->amount;

            if ($remitting_amount > $loan_details->amount) {
                throw new Error('The amount trying to remit is higher than the loaned principal amount');
            }

            $due_payment_details = $pending_payments->first();

            // TODO: Update payment history as well

            if ($remitting_amount < $due_payment_details->amount) {
                throw new Error('Insufficient amount. Due amount is ' . $loan_details->currency . ' ' . $due_payment_details->amount);
            } elseif ($remitting_amount == $due_payment_details->amount) {
                $this->updatePaymentScheduleAsPaid($loan_details->id, $due_payment_details->id, $pending_payment_terms);
            } elseif ($remitting_amount > $due_payment_details->amount) {
                if ($pending_payment_terms == 1) {
                    throw new Error('Payment exceeds the due amount. Due amount is ' . $loan_details->currency . ' ' . $due_payment_details->amount);
                }

                $excess_amount = $remitting_amount - $due_payment_details->amount;

                $this->recalculatePaymentSchedule($loan_details, $remitting_amount, $pending_payment_terms, $due_payment_details->id);
                $this->updatePaymentScheduleAsPaid($loan_details->id, $due_payment_details->id, $pending_payment_terms);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Error('Failed to account the repayment, Error ' . $e);
        }
    }

    private function recalculatePaymentSchedule($loan_details, $remitting_amount, $pending_payment_terms, $due_payment_id)
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

        RepaymentSchedule::where('repayment_schedule.status', 'PENDING')
            ->where('repayment_schedule.id', '!=', $due_payment_id)
            ->update([
                'amount' => $revised_installment,
            ]);
    }

    private function updatePaymentScheduleAsPaid($loan_id, $repayment_schedule_id, $pending_payment_terms)
    {
        try {
            DB::beginTransaction();
            $update_payment_schedule = RepaymentSchedule::where('repayment_schedule.id', $repayment_schedule_id)
            ->update([
                'status' => 'PAID',
            ]);

            if ($pending_payment_terms == 1) {
                Loan::where('loan.id', $loan_id)
                    ->update([
                        'status' => 'PAID',
                    ]);
            }

            DB::commit();

            return $update_payment_schedule;
        } catch (Exception $e) {

            DB::rollBack();
            throw new Error('Failed to update payment status, Please try again later, Error : ' . $e);
        }
    }
}
