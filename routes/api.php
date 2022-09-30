<?php

use App\Http\Controllers\API\V1\DashboardController;
use App\Http\Controllers\API\V1\FloatAccountController;
use App\Http\Controllers\API\V1\MpesaController;
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

Route::middleware('auth.jwt')->prefix('/v1')->group(function() {
    Route::prefix('/payments')->group(function() {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{payment}', [PaymentController::class, 'show']);

        Route::post('/', PaymentController::class);
        Route::post('/voucher/credit', [VoucherController::class, 'credit']);
        Route::post('/voucher/disburse', [VoucherController::class, 'disburse']);

        Route::post('/withdraw', [PaymentController::class, 'withdraw']);
    });

    Route::get('/accounts/{accountId}/vouchers', [VoucherController::class, 'getAccountVouchers']);

    //  DASHBOARD ROUTES
    Route::get('/dashboard', DashboardController::class);
    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'revenueChart']);

    Route::prefix('/vouchers')->group(function() {
        Route::get('/', [VoucherController::class, 'index']);
        Route::get('/transactions', [VoucherController::class, 'getTransactions']);
        Route::get('/{voucher}', [VoucherController::class, 'show']);
    });

    Route::get('/mpesa/payments', [MpesaController::class, 'getBySubType']);

    Route::prefix('/float-accounts')->group(function() {
        Route::get('/', [FloatAccountController::class, 'index']);
        Route::post('/', [FloatAccountController::class, 'store']);
        Route::get('/transactions', [FloatAccountController::class, 'getTransactions']);
        Route::get('/{floatAccount}', [FloatAccountController::class, 'show']);

        Route::post('/{floatAccount}/top-up', [FloatAccountController::class, 'topUp']);
    });
});

Route::middleware('throttle:3,60')->prefix('/v1')->group(function() {
    Route::get('payments/mpesa/status/query', [PaymentController::class, 'queryMpesaStatus'])
        ->name('payments.mpesa.status.query');
});
