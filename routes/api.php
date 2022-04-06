<?php

use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\VoucherController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function(Request $request) {
    return $request->user();
});

Route::post('/nats', [PaymentController::class, 'nats']);

Route::prefix('/v1')->group(function() {
    Route::prefix('/payments')->group(function() {
        Route::post('/', PaymentController::class);
        Route::post('/voucher/credit', [VoucherController::class, 'credit']);
        Route::post('/voucher/debit', [VoucherController::class, 'deposit']);
        Route::post('/voucher/disburse', [VoucherController::class, 'disburse']);

        Route::get("/details/{transactionId}/{accountId}", [PaymentController::class, "findDetails"]);
    });
});
