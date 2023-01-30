<?php

namespace App\Providers;

use App\Policies\LoanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('create-loan', [LoanPolicy::class, 'createLoan']);
        Gate::define('view-loan-customer', [LoanPolicy::class, 'viewLoanCustomer']);
        Gate::define('view-loan-admin', [LoanPolicy::class, 'viewLoanAdmin']);
        Gate::define('view-loan-by-id', [LoanPolicy::class, 'viewLoanById']);
        Gate::define('approve-loan', [LoanPolicy::class, 'changeLoanStatus']);
        Gate::define('reject-loan', [LoanPolicy::class, 'changeLoanStatus']);
        Gate::define('repay-loan-customer', [LoanPolicy::class, 'repayLoanCustomer']);
    }
}

