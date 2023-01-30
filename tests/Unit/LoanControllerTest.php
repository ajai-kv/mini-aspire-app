<?php

namespace Tests\Controllers;

use App\Enums\UserType;
use App\Http\Controllers\Api\LoanController;
use App\Http\Services\LoanService;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Str;


class LoanControllerTest extends TestCase
{
    public function testGetLoanByCustomer()
    {

        $user = User::factory()->make();

        $loan_input = [
            'term' => '6',
            'currency' => 'INR',
            'amount' => '85000',
        ];

        $loan_reponse = [
            "loan_reference_number" => 20,
            "tenure" => 6,
            "tenure_type" => "WEEKLY",
            "currency" => "INR",
            "amount" => 85000,
            "status" => "PENDING"
        ];


        $customer_test = [
            "id" => "9857be86-50d5-4632-ac3d-82ae6de18ba5",
            "user_id" => "9857be86-500b-486b-b637-ea9eba630424",
            "date_of_birth" => "1998-10-09",
            "address" => "Test 1, Test XYZ, Claifornia",
            "identification_document" => "PASSPORT",
            "document_reference_number" => "S110038A9",
            "deleted_at" => null,
            "created_at" => "2023-01-29T21:04:20.000000Z",
            "updated_at" => "2023-01-29T21:04:20.000000Z"
        ];

        $this->actingAs($user)->json('get', 'api/loan')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                        "loans" => [
                            "*" => [
                                'loan_reference_number',
                                'tenure',
                                'tenure_type',
                                'currency',
                                'amount',
                                'status'
                            ]
                        ]
                    ]
                ]
            );
    }

    public function testGetLoanByAdmin()
    {
        $user = User::make([
            'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '9846889299',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'type' => UserType::ADMIN->value,
            'is_active' => true
        ]);

        $loan_input = [
            'status' => 'PENDING'
        ];

        $loan_reponse = [
            "loan_reference_number" => 20,
            "tenure" => 6,
            "tenure_type" => "WEEKLY",
            "currency" => "INR",
            "amount" => 85000,
            "status" => "PENDING"
        ];


        $customer_test = [
            "id" => "9857be86-50d5-4632-ac3d-82ae6de18ba5",
            "user_id" => "9857be86-500b-486b-b637-ea9eba630424",
            "date_of_birth" => "1998-10-09",
            "address" => "Test 1, Test XYZ, Claifornia",
            "identification_document" => "PASSPORT",
            "document_reference_number" => "S110038A9",
            "deleted_at" => null,
            "created_at" => "2023-01-29T21:04:20.000000Z",
            "updated_at" => "2023-01-29T21:04:20.000000Z"
        ];

        $this->actingAs($user)->json('get', 'api/loan/admin')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                        "loans" => [
                            "*" => [
                                'loan_reference_number',
                                'tenure',
                                'tenure_type',
                                'currency',
                                'amount',
                                'status'
                            ]
                        ]
                    ]
                ]
            );
    }

    public function testCreateLoanSuccessfully()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();
        $user = User::factory()->make();

        $loanControllerStub  = $this->createStub(LoanController::class);
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

        $this->assertSame($data, $loanServiceStub->createLoan($loanInput, $user));
        $loanControllerStub->method('createLoanApplication')
            ->willReturn($response);

        $this->assertSame($response, $loanControllerStub->createLoanApplication($request));
    }

    public function testFailToCreateLoanIfUnauthorized()
    {
        $user = User::make([
            'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '9846889299',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'type' => UserType::CUSTOMER->value,
            'is_active' => true
        ]);

        $customer_test = Customer::make([
            "id" => "9857be86-50d5-4632-ac3d-82ae6de18ba5",
            "user_id" => "9857be86-500b-486b-b637-ea9eba630424",
            "date_of_birth" => "1998-10-09",
            "address" => "Test 1, Test XYZ, Claifornia",
            "identification_document" => "PASSPORT",
            "document_reference_number" => "S110038A9",
            "deleted_at" => null,
            "created_at" => "2023-01-29T21:04:20.000000Z",
            "updated_at" => "2023-01-29T21:04:20.000000Z"
        ]);

        $loan_input = [
            'term' => '6',
            'currency' => 'INR',
            'amount' => '85000',
        ];

        $loanServiceStub  = $this->createStub(LoanService::class);

        $create_loan_response = [
            'loan_reference_number' => 1,
            'tenure' => 3,
            'tenure_type' => 'WEEKLY',
            'currency' => 'USD',
            'amount' => 1000,
            'status' => 'PENDING'
        ];

        $loanServiceStub->method('createLoan')
            ->willReturn($create_loan_response);


        $this->json('post', 'api/loan', $loan_input)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testToBlockUserFromApprovingLoanIfNotAdmin()
    {
        $user = User::make([
            'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '9846889299',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'type' => UserType::CUSTOMER->value,
            'is_active' => true
        ]);

        $customer_test = Customer::make([
            "id" => "9857be86-50d5-4632-ac3d-82ae6de18ba5",
            "user_id" => "9857be86-500b-486b-b637-ea9eba630424",
            "date_of_birth" => "1998-10-09",
            "address" => "Test 1, Test XYZ, Claifornia",
            "identification_document" => "PASSPORT",
            "document_reference_number" => "S110038A9",
            "deleted_at" => null,
            "created_at" => "2023-01-29T21:04:20.000000Z",
            "updated_at" => "2023-01-29T21:04:20.000000Z"
        ]);

        $loan_number = '6';

        $loanServiceStub  = $this->createStub(LoanService::class);

        $loanServiceStub->method('approveLoan')
            ->willReturn('Approved successfully');


        $this->actingAs($user)->json('put', 'api/loan/'.$loan_number.'/approve')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_if_it_create_new_loan_application()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();
        $user = User::factory()->make();

        $loanControllerStub  = $this->createStub(LoanController::class);
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
        $loanControllerStub->method('createLoanApplication')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->createLoanApplication($request));
    }

    public function test_to_approve_loan_application()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();
        $loan_number='999';
        $user = User::factory()->make();

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

        $approved_message='Approved successfully';        
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('approveLoan')
              ->willReturn($approved_message);
        $this->assertSame($approved_message, $loanServiceStub->approveLoan($loan_number,$user));

        $loanControllerStub->method('approveLoanApplication')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->approveLoanApplication($request,$loan_number));
    }

    public function test_to_reject_loan_application()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();
        $loan_number='999';
        $user = User::factory()->make();

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

        $reject_reason='Reason for rejection';
        $rejected_message='Rejected successfully';
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('rejectLoan')
              ->willReturn($rejected_message);
        $this->assertSame($rejected_message, $loanServiceStub->rejectLoan($loan_number,$reject_reason,$user));

        $loanControllerStub->method('rejectLoanApplication')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->rejectLoanApplication($request,$loan_number));
    }

    public function test_to_get_loan_from_customer()
    {
        $response = new Response();

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

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


        $loanControllerStub->method('getLoansByCustomer')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->getLoansByCustomer());
    }

    public function test_to_get_loan_for_admin_users()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

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
        $this->assertSame($loan, $loanServiceStub->getLoansByAdmin($loan['status']));


        $loanControllerStub->method('getLoansByAdmin')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->getLoansByAdmin($request));
    }

    public function test_to_get_loan_by_loan_id()
    {
        $response = new Response();

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

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

        $loanControllerStub->method('getLoanById')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->getLoanById('1'));
    }

    public function test_to_receive_payment_for_loan()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();

        $user = User::make([
            'id' => '3f33ebbb-e1c2-450b-9879-382895554480',
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '9846889299',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'type' => UserType::CUSTOMER->value,
            'is_active' => true
        ]);

        $loan_number='999';

        $loanControllerStub  = $this->createStub(LoanController::class);
        $loanServiceStub  = $this->createStub(LoanService::class);

        $repayment_details = [
            'loan_number' => 'eba5801e-b19b-4a0d-89f6-fe13cf178a7d',
            'currency' => 'USD',
            'amount' => 1000,
        ];
        $success_message='Payment accounted successfully';
        $loanServiceStub  = $this->createStub(LoanService::class);
        $loanServiceStub->method('processRepayment')
              ->willReturn($success_message);
        $this->assertSame($success_message, $loanServiceStub->processRepayment($repayment_details, $user));

        $loanControllerStub->method('receivePaymentOnLoan')
              ->willReturn($response);
 
        $this->assertSame($response, $loanControllerStub->receivePaymentOnLoan($request,$loan_number));
    }
}
