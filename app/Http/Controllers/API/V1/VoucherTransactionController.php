<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\VoucherTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $transactions = VoucherTransaction::select(['id', 'type', 'amount', 'description', 'voucher_id', 'created_at'])
            ->latest()
            ->limit($request->integer('limit', 1000));

        if (in_array('voucher', $relations)) {
            $transactions->with('voucher:id,account_id,voucher_type_id,balance');
        }

        if ($id = $request->integer('account_id')) {
            $transactions->whereHas('voucher.voucherType', function(Builder $qry) use ($id) {
                $qry->whereAccountId($id);
            });
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
