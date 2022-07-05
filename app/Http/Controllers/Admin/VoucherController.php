<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VoucherController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $relations = explode(",", $request->query("with"));

        $vouchers = Voucher::latest();

        if(in_array("voucher_transactions", $relations)) {
            $vouchers = $vouchers->with("voucherTransactions:id,voucher_id,type,amount,description,created_at");
        }

        return VoucherResource::collection($vouchers->get());
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $relations = explode(",", $request->query("with"));

        $transactions = new VoucherTransaction;

        if(in_array("voucher", $relations)) {
            $transactions = $transactions->with("voucher:id,account_id,type,balance");
        }

        return response()->json($transactions->get());
    }
}
