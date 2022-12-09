<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\FloatAccountTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloatAccountTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $transactions = FloatAccountTransaction::select([
            'id',
            'type',
            'amount',
            'description',
            'float_account_id',
            'created_at',
        ])->limit($request->integer('limit', 1000))->latest();

        if ($id = $request->integer('float_account_id')) {
            $transactions->whereFloatAccountId($id);
        }

        if (in_array('float_account', $relations)) {
            $transactions->with('floatAccount:id,balance,floatable_type,floatable_id,updated_at');
        }

        if (in_array('payment', $relations)) {
            $transactions->with('payment:id,amount,status,type,subtype,description,account_id,provider_id,updated_at');
        }

        return $this->successResponse($transactions->get());
    }

    public function show(FloatAccountTransaction $transaction, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('float_account', $relations)) {
            $transaction->load('floatAccount:id,account_id,balance');
        }

        return $this->successResponse($transaction->toArray());
    }
}
