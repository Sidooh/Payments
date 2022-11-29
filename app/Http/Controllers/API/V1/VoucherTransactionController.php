<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\VoucherTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $transactions = VoucherTransaction::select(['id', 'type', 'amount', 'description', 'voucher_id', 'created_at'])
            ->latest()
            ->limit(1000);

        if (in_array('voucher', $relations)) {
            $transactions->with('voucher:id,account_id,voucher_type_id,balance');
        }

        return $this->successResponse($transactions->get());
    }


    public function show(VoucherTransaction $transaction, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('voucher', $relations)) {
            $transaction->load('voucher:id,account_id,voucher_type_id,balance');
        }

        return $this->successResponse($transaction->toArray());
    }

}
