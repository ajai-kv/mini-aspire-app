<?php

namespace App\Policies;

use App\Enums\UserType;

class LoanPolicy
{
    public function createLoan()
    {
        $user = auth()->user();
        if ($user->type !== UserType::CUSTOMER->value || !($user->is_active)) {
            return false;
        }
        return true;
    }

    public function viewLoanCustomer()
    {
        $user = auth()->user();
        if ($user->type !== UserType::CUSTOMER->value || !($user->is_active)) {
            return false;
        }
        return true;
    }

    public function viewLoanAdmin()
    {
        $user = auth()->user();
        if ($user->type !== UserType::ADMIN->value || !($user->is_active)) {
            return false;
        }
        return true;
    }
    
    public function changeLoanStatus()
    {
        $user = auth()->user();
        if ($user->type !== UserType::ADMIN->value || !($user->is_active)) {
            return false;
        }
        return true;
    }

    public function viewLoanById()
    {
        $user = auth()->user();
        if (!($user->is_active)) {
            return false;
        }
        return true;
    }

    public function repayLoanCustomer()
    {
        $user = auth()->user();
        if ($user->type !== UserType::CUSTOMER->value  || !($user->is_active)) {
            return false;
        }
        return true;
    }

    public function viewRepaymentSchedule()
    {
        $user = auth()->user();
        if (!($user->is_active)) {
            return false;
        }
        return true;
    }
}
