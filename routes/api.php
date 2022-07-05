<?php

use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth.jwt')->prefix('/v1')->group(function () {
    Route::prefix('/payments')->group(function () {
        Route::get('/', [PaymentController::class, "index"]);
        Route::get('/transaction/{transactionId}', [PaymentController::class, "getByTransactionId"]);

        Route::post('/', PaymentController::class);
        Route::post('/voucher/credit', [VoucherController::class, 'credit']);
        Route::post('/voucher/debit', [VoucherController::class, 'deposit']);
        Route::post('/voucher/disburse', [VoucherController::class, 'disburse']);

        Route::get("/details/{transactionId}/{accountId}", [PaymentController::class, "findDetails"]);
    });

    Route::get('/accounts/{accountId}/vouchers', [VoucherController::class, "getAccountVouchers"]);
    Route::get('/vouchers/{voucher}', [VoucherController::class, "show"]);

    //  DASHBOARD ROUTES
    Route::get('/dashboard', DashboardController::class);
});

Route::middleware('throttle:3,60')->prefix('/v1')->group(function () {
    Route::get('payments/mpesa/status/query', [PaymentController::class, "queryMpesaStatus"])->name('payments.mpesa.status.query');
});


