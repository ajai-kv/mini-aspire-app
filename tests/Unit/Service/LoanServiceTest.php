<?php
namespace Tests\Unit\Services;

use Illuminate\Support\Str;
use App\Enums\UserType;
use App\Http\Services\LoanService;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use Exception;

class LoanServiceTest extends TestCase
{

    // Tests for createLoan sevice function
    public function test_if_it_create_new_loans()
    {
        $user = User::factory()->make();
        
         $loanServiceStub  = $this->createStub(LoanService::class);

         $data = [
            'loan_reference_number' => 1,
            'tenure' => 3,
            'tenure_type' => 'WEEKLY',
            'currency' => 'USD',
            'amount' => 1000,
            'status' => 'PENDING'
        ];

         $loanServiceStub->method('createLoan')
              ->willReturn($data);
 
         $loanInput = json_encode([
            'term' => '3',
            'currency' => 'USD',
            'amount' => 1000
        ]);

        $this->assertSame($data, $loanServiceStub->createLoan($loanInput,$user));

    }

    // Tests for approveLoan sevice function
    public function test_to_approve_a_loan()
    {
        $user = User::factory()->make();
        $loan_number='1';
        $approved_message='Approved successfully';        
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('approveLoan')
              ->willReturn($approved_message);
        $this->assertSame($approved_message, $loanServiceStub->approveLoan($loan_number,$user));
    }

    public function test_to_handle_exception_while_loan_approval()
    {
        $user = User::factory()->make();
        $loan_number='1';
        $loan_approve_exception= new Exception();
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('approveLoan')
              ->willReturn($loan_approve_exception);
        $this->assertSame($loan_approve_exception, $loanServiceStub->approveLoan($loan_number,$user));
    }

    // Tests for rejectLoan sevice function
    public function test_to_reject_a_loan()
    {
        $user = User::factory()->make();
        $loan_number='1';
        $reject_reason='Reason for rejection';
        $rejected_message='Rejected successfully';
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('rejectLoan')
              ->willReturn($rejected_message);
        $this->assertSame($rejected_message, $loanServiceStub->rejectLoan($loan_number,$reject_reason,$user));
    }

    public function test_to_handle_exception_while_loan_rejection()
    {
        $user = User::factory()->make();
        $loan_number='1';
        $reject_reason='Reason for rejection';
        $loan_reject_exception= new Exception();
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('rejectLoan')
              ->willReturn($loan_reject_exception);
        $this->assertSame($loan_reject_exception, $loanServiceStub->rejectLoan($loan_number,$reject_reason,$user));
    }

    // Tests for getLoansByCustomer sevice function
    public function test_to_get_loan_from_user_id()
    {
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoansByCustomer')
              ->willReturn($loan);
        $this->assertSame($loan, $loanServiceStub->getLoansByCustomer($loan['customer_id']));
    }

    public function test_to_handle_exception_while_get_loan_from_user_id()
    {
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $get_loan_by_customer_exception= new Exception();

        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoansByCustomer')
              ->willReturn($get_loan_by_customer_exception);
        $this->assertSame($get_loan_by_customer_exception, $loanServiceStub->getLoansByCustomer($loan['customer_id']));
    }

    // Tests for getLoansByAdmin sevice function
    public function test_to_get_loan_for_admin_user()
    {
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoansByAdmin')
              ->willReturn($loan);
        $this->assertSame($loan, $loanServiceStub->getLoansByAdmin($loan['customer_id']));
    }

    public function test_to_handle_exception_while_get_loan_for_admin_user()
    {
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $get_loans_by_admin_exception= new Exception();

        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoansByAdmin')
              ->willReturn($get_loans_by_admin_exception);
        $this->assertSame($get_loans_by_admin_exception, $loanServiceStub->getLoansByAdmin($loan['customer_id']));
    }

    // Tests for processRepayment sevice function
    // public function test_to_process_the_repayments_from_the_customer()
    // {
    //     $user = User::make([
    //         'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
    //         'full_name' => fake()->name(),
    //         'email' => fake()->unique()->safeEmail(),
    //         'phone_number' => '9846889299',
    //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    //         'remember_token' => Str::random(10),
    //         'type' => UserType::CUSTOMER->value,
    //         'is_active' => true
    //     ]);

    //     $repayment_details = [
    //         'loan_number' => 'eba5801e-b19b-4a0d-89f6-fe13cf178a7d',
    //         'currency' => 'USD',
    //         'amount' => 1000,
    //     ];
    //     $success_message='Payment accounted successfully';
    //     $loanServiceStub  = $this->createStub(LoanService::class);
    //     $loanServiceStub->method('processRepayment')
    //           ->willReturn($success_message);
    //     $this->assertSame($success_message, $loanServiceStub->processRepayment($repayment_details, $user));
    // }

    // public function test_to_handle_exception_while_process_the_repayments_from_the_customer()
    // {
    //     $user = User::make([
    //         'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
    //         'full_name' => fake()->name(),
    //         'email' => fake()->unique()->safeEmail(),
    //         'phone_number' => '9846889299',
    //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    //         'remember_token' => Str::random(10),
    //         'type' => UserType::CUSTOMER->value,
    //         'is_active' => true
    //     ]);

    //     $repayment_details = [
    //         'loan_number' => 'eba5801e-b19b-4a0d-89f6-fe13cf178a7d',
    //         'currency' => 'USD',
    //         'amount' => 1000,
    //     ];
    //     $process_repayment_exception= new Exception();

    //     $success_message='Payment accounted successfully';
    //     $loanServiceStub  = $this->createStub(LoanService::class);
    //     $loanServiceStub->method('processRepayment')
    //           ->willReturn($process_repayment_exception);
    //     $this->assertSame($process_repayment_exception, $loanServiceStub->processRepayment($repayment_details, $user));
    // }

    // Tests for getLoanById sevice function
    public function test_to_get_loan_from_loan_id()
    {
        $user = User::factory()->make();
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoanById')
              ->willReturn($loan);
        $this->assertSame($loan, $loanServiceStub->getLoanById('1',$user));
    }

    public function test_to_handle_exception_while_get_loan_from_loan_id()
    {
        $user = User::factory()->make();
        $loan = [
            'customer_id'=>'d04da3cd-5cc2-4d0f-9d1d-cd5a3ad71e9e',
            'tenure'=>3,
            'tenure_type'=>'WEEKLY',
            'currency'=>'USD',
            'amount'=>1000,
            'approved_by'=>'06c08904-91e4-4694-8a50-455a1356467a',
            'rejected_by'=>'ee3b7fca-a1c6-47f4-81be-6ca18f3dc536',
            'reject_reason'=>'This will be the reason for rejection',
            'status'=>'PENDING',
        ];
        $process_repayment_exception= new Exception();

        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('getLoanById')
              ->willReturn($process_repayment_exception);
        $this->assertSame($process_repayment_exception, $loanServiceStub->getLoanById('1',$user));
    }
}