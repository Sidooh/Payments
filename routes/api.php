<?php

use App\Http\Controllers\API\V1\AdminController;
use App\Http\Controllers\API\V1\DashboardController;
use App\Http\Controllers\API\V1\FloatAccountController;
use App\Http\Controllers\API\V1\FloatAccountTransactionController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\VoucherController;
use App\Http\Controllers\API\V1\VoucherTransactionController;
use App\Http\Controllers\API\V1\VoucherTypeController;
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

Route::middleware('throttle:5,60')->prefix('/v1')->group(function() {
    Route::get('payments/mpesa/status/query', [PaymentController::class, 'queryMpesaStatus'])
        ->name('payments.mpesa.status.query');
});

//=========================================================================================================
// API
//=========================================================================================================

Route::middleware('auth.jwt')->prefix('/v1')->group(function() {
    Route::prefix('/payments')->group(function() {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', PaymentController::class);
        Route::post('/merchant', [PaymentController::class, 'merchant']);
        Route::post('/withdraw', [PaymentController::class, 'withdraw']);

        Route::middleware('throttle:api')
            ->get('/{payment}', [PaymentController::class, 'show']);

        Route::get('/providers/{type}/{subtype}', [PaymentController::class, 'typeAndSubtype']);
    });

    Route::apiResource('voucher-types', VoucherTypeController::class)
        ->only(['index', 'show', 'store']);
    Route::prefix('/voucher-types')->group(function () {
        Route::post('/{voucher_type}/disburse', [VoucherTypeController::class, 'disburse']);
    });

    Route::prefix('/vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::post('/', [VoucherController::class, 'store']);
        Route::get('/{voucher}', [VoucherController::class, 'show']);
        Route::post('/credit', [VoucherController::class, 'credit']);
    });

    Route::prefix('/voucher-transactions')->group(function () {
        Route::get('/', [VoucherTransactionController::class, 'index']);
        Route::get('/{transaction}', [VoucherTransactionController::class, 'show']);
    });

    Route::prefix('/float-accounts')->group(function() {
        Route::get('/', [FloatAccountController::class, 'index']);
        Route::post('/', [FloatAccountController::class, 'store']);
        Route::get('/{float_account}', [FloatAccountController::class, 'show']);
        Route::post('/credit', [FloatAccountController::class, 'credit']);
    });

    Route::prefix('/float-account-transactions')->group(function () {
        Route::get('/', [FloatAccountTransactionController::class, 'index']);
        Route::get('/{transaction}', [FloatAccountTransactionController::class, 'show']);
    });

    Route::prefix('/admin')->group(function() {
        Route::post('/app', AdminController::class);
    });

    //  DASHBOARD ROUTES
    Route::prefix('/dashboard')->group(function() {
        Route::get('/', DashboardController::class);
        Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
    });

});
