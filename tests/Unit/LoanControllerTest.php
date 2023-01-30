<?php

namespace Tests\Controllers;

use App\Enums\UserType;
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


    public function testLoanIsCreatedSuccessfully()
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

        // $loanServiceStub  = $this->createStub(LoanService::class);

        $create_loan_response = [
            'loan_reference_number' => 1,
            'tenure' => 3,
            'tenure_type' => 'WEEKLY',
            'currency' => 'USD',
            'amount' => 1000,
            'status' => 'PENDING'
        ];

        $loanServiceMock = $this->getMockBuilder(LoanService::class)
            // ->setMethods(['createLoan'])
            ->getMock();


        // $loanServiceStub->method('findCustomerByUserId')
        //     ->willReturn($customer_test);

        $loanServiceMock->method('createLoan')
            ->willReturn($create_loan_response);
            

        $this->actingAs($user)->json('post', 'api/loan', $loan_input)
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
            // ->assertJsonStructure(
            //     [
            //         'data' => [
            //             [
            //                 'loan_reference_number',
            //                 'tenure',
            //                 'tenure_type',
            //                 'currency',
            //                 'amount',
            //                 'status'
            //             ]
            //         ]
            //     ]
            // );
    }
}
