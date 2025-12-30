<?php

namespace App\Providers;

use App\Models\CashAccount;
use App\Models\CreditCard;
use App\Models\Loan;
use App\Policies\CashAccountPolicy;
use App\Policies\CreditCardPolicy;
use App\Policies\LoanPolicy;
use Modules\Transaction\Models\Transaction;
use Modules\Transaction\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CashAccount::class => CashAccountPolicy::class,
        CreditCard::class => CreditCardPolicy::class,
        Loan::class => LoanPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
