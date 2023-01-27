<?php
namespace App\Http\Services;

use App\Models\Loan;
use App\Models\RepaymentSchedule;
use Error;
use Exception;
use Illuminate\Support\Facades\DB;


class LoanService {
    public function createLoan($loan_input){
        $loan_input = json_decode($loan_input);
        $loan = Loan::create([
            'customer_id' => '0316453b-58ff-44fd-8c71-9d54343575c6',
            'tenure' => $loan_input->term,
            'tenure_type' => 'WEEKLY',
            'currency' => $loan_input->currency,
            'amount' => $loan_input->amount,
            'status' => 'PENDING'
        ]);

        $loan->refresh();


        $data = [
            'loan_reference_number' => $loan->loan_reference_number,
            'tenure' => $loan->tenure,
            'tenure_type' => $loan->tenure_type,
            'currency' => $loan->currency,
            'amount' => $loan->amount,
            'status' => $loan->status
        ];

        return $data;

    }
    
    public function approveLoan(String $loan_number)
    {
        try {

            $loan = Loan::where('loan_reference_number', $loan_number)->firstOrFail();

            if($loan->status == 'APPROVED'){
                throw new Error('Loan '.$loan_number.' is already approved');
            }
    
            DB::beginTransaction();
    
            $updatedLoan = $this->updateLoanAsApproved($loan);
            $this->calculateRepaymentSchedules($updatedLoan);
    
            DB::commit();
            
            return 'Approved successfully';

        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function updateLoanAsApproved(Loan $loan){
        $loan->status = 'APPROVED';
        $loan->approved_by = '6b6205bd-60ed-49e8-be36-9cbc61b24ea7';
        $loan->save();
        $loan->refresh();
        return $loan;
    }


    private function calculateRepaymentSchedules(Loan $loan_details){
        $total_amount = $loan_details->amount;
        $total_term = $loan_details->tenure;
        $installment = ($total_amount)/($total_term);

        $repayment_schedules = array();

        for($each_term = 1; $each_term <= $total_term; $each_term++){

            $schedule = $this->generateRepaymentSchedule($loan_details->id, $each_term, $installment);
            $repayment_schedules[] = $schedule;

        }

        return $repayment_schedules;
    }

    private function generateRepaymentSchedule($loan_id, $term, $installment){

        $due_date = date('Y-m-d H:i:s',strtotime('+'.$term.'weeks'));

        $repayment_schedule = RepaymentSchedule::create([
            'loan_id' => $loan_id,
            'amount' => $installment,
            'due_date' => $due_date,
            'status' => 'PENDING'
        ]);

        $repayment_schedule->refresh();

        return $repayment_schedule;
    }
}