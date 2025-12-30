<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * Service Provider for binding application services to the container.
 * This enables dependency injection throughout the application.
 */
class ServiceBindingProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array<string, string>
     */
    public $singletons = [
        // Account Services
        \App\Services\Accounts\StoreAccount::class => \App\Services\Accounts\StoreAccount::class,
        \App\Services\CashAccounts\IndexCashAccounts::class => \App\Services\CashAccounts\IndexCashAccounts::class,
        \App\Services\CreditCards\IndexCreditCards::class => \App\Services\CreditCards\IndexCreditCards::class,
        \App\Services\CreditCards\UpdateCreditCard::class => \App\Services\CreditCards\UpdateCreditCard::class,
        \App\Services\Loans\IndexLoans::class => \App\Services\Loans\IndexLoans::class,
        \App\Services\Loans\UpdateLoanAccount::class => \App\Services\Loans\UpdateLoanAccount::class,
        
        // Category Services
        \App\Services\Categories\StoreCategory::class => \App\Services\Categories\StoreCategory::class,
        \App\Services\Categories\UpdateCategory::class => \App\Services\Categories\UpdateCategory::class,
        \App\Services\Categories\DeleteCategory::class => \App\Services\Categories\DeleteCategory::class,
        
        // Group Services
        \App\Services\Groups\IndexGroups::class => \App\Services\Groups\IndexGroups::class,
        
        // Rule Services
        \App\Services\Rules\StoreRule::class => \App\Services\Rules\StoreRule::class,
        
        // Budget & Cash Flow Services
        \App\Services\Budget\BudgetService::class => \App\Services\Budget\BudgetService::class,
        \App\Services\Budget\BudgetAggregationService::class => \App\Services\Budget\BudgetAggregationService::class,
        \App\Services\Budget\BudgetUtilizationService::class => \App\Services\Budget\BudgetUtilizationService::class,
        \App\Services\CashFlow\CashFlowService::class => \App\Services\CashFlow\CashFlowService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Transaction Module Services
        $this->app->singleton(
            \Modules\Transaction\Services\IndexTransactions::class,
            \Modules\Transaction\Services\IndexTransactions::class
        );
        
        $this->app->singleton(
            \Modules\Transaction\Services\StoreTransaction::class,
            \Modules\Transaction\Services\StoreTransaction::class
        );
        
        $this->app->singleton(
            \Modules\Transaction\Services\UpdateTransaction::class,
            \Modules\Transaction\Services\UpdateTransaction::class
        );
        
        $this->app->singleton(
            \Modules\Transaction\Services\ImportTransactions::class,
            \Modules\Transaction\Services\ImportTransactions::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
