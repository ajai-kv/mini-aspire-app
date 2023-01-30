<?php

namespace Tests\Unit\Services;

use App\Enums\UserType;
use App\Http\Services\LoanService;
use App\Models\User;
use PHPUnit\Framework\TestCase;


class LoanServiceTest extends TestCase
{
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

        $this->assertSame($data, $loanServiceStub->createLoan($loanInput, $user));
    }
}
