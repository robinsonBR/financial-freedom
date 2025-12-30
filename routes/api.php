<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->name('api.v1.')->middleware('auth:sanctum')->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    // Future API endpoints can be added here following RESTful conventions
    // Examples:
    // Route::apiResource('cash-accounts', CashAccountApiController::class);
    // Route::apiResource('credit-cards', CreditCardApiController::class);
    // Route::apiResource('loans', LoanApiController::class);
    // Route::apiResource('transactions', TransactionApiController::class);
    // Route::apiResource('categories', CategoryApiController::class);
});
