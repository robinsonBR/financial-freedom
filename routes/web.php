<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Rules
    Route::prefix('rules')->name('rules.')->group(function () {
        Route::get('/', [RulesController::class, 'index'])
            ->name('index');
        Route::post('/', [RulesController::class, 'store'])
            ->name('store');
        Route::put('/{rule}', [RulesController::class, 'update'])
            ->name('update');
        Route::delete('/{rule}', [RulesController::class, 'destroy'])
            ->name('destroy');
    });

    // Financial Overview Routes
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/cash-flow', [CashFlowController::class, 'index'])
            ->name('cash-flow');
        Route::get('/budget', [BudgetController::class, 'index'])
            ->name('budget');
    });

    // Reports & Analytics
    Route::get('/reports', [ReportsController::class, 'index'])
        ->name('reports.index');

    // Account Management Routes
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])
            ->name('index');
        Route::post('/', [AccountController::class, 'store'])
            ->name('store');
        Route::delete('/{account}', [AccountController::class, 'destroy'])
            ->name('destroy');

        // Cash Accounts
        Route::prefix('cash')->name('cash.')->group(function () {
            Route::get('/{cashAccount}', [CashAccountController::class, 'show'])
                ->name('show');
            Route::put('/{cashAccount}', [CashAccountController::class, 'update'])
                ->name('update');
        });

        // Credit Cards
        Route::prefix('credit-cards')->name('credit-cards.')->group(function () {
            Route::get('/{creditCard}', [CreditCardController::class, 'show'])
                ->name('show');
            Route::put('/{creditCard}', [CreditCardController::class, 'update'])
                ->name('update');
        });

        // Loans
        Route::prefix('loans')->name('loans.')->group(function () {
            Route::get('/{loan}', [LoanController::class, 'show'])
                ->name('show');
            Route::put('/{loan}', [LoanController::class, 'update'])
                ->name('update');
        });
    });

    // Goals Resource
    Route::resource('goals', GoalsController::class)
        ->except(['create', 'edit']);

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])
            ->name('index');

        Route::put('/portfolio', [PortfolioController::class, 'update'])
            ->name('portfolio.update');

        // Categories Resource
        Route::resource('categories', CategoryController::class)
            ->except(['create', 'edit', 'show']);

        // Groups Resource
        Route::resource('groups', GroupsController::class)
            ->only(['store', 'update', 'destroy']);

        // Institutions Resource
        Route::resource('institutions', InstitutionController::class)
            ->except(['create', 'edit', 'show']);
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');
    });
});

require __DIR__.'/auth.php';
