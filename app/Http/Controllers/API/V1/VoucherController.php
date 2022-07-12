<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Description;
use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Repositories\VoucherRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class VoucherController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $relations = explode(",", $request->query("with"));

        $vouchers = Voucher::latest();

        if(in_array("voucher_transactions", $relations)) {
            $vouchers = $vouchers->with("voucherTransactions:id,voucher_id,type,amount,description,created_at");
        }

        $vouchers = $vouchers->get();

        if(in_array("account", $relations)) {
            $vouchers = withRelation("account", $vouchers, "account_id", "id");
        }

        return VoucherResource::collection($vouchers);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $relations = explode(",", $request->query("with"));

        $transactions = VoucherTransaction::latest();

        if(in_array("voucher", $relations)) {
            $transactions = $transactions->with("voucher:id,account_id,type,balance");
        }

        if(in_array("payment", $relations)) {
            $transactions = $transactions->with("payment:id,providable_id,providable_type,status");
        }

        return response()->json($transactions->get());
    }

    public function show(Voucher $voucher): array
    {
        return $voucher->toArray();
    }

    public function getAccountVouchers(int $accountId): array
    {
        $vouchers = Voucher::select(["id", "type", "balance"])->whereAccountId($accountId)->get();

        return $vouchers->toArray();
    }

    public function credit(Request $request): array
    {
        $request->validate([
            'account_id'  => ['required'],
            'amount'      => ['required'],
            "description" => ["required", "string"],
            "notify"      => ['required', 'boolean']
        ]);

        $accountId = $request->input("account_id");
        $amount = $request->input("amount");
        $description = Description::from($request->input("description"));
        $notify = $request->boolean("notify");

        return VoucherRepository::credit($accountId, $amount, $description, $notify);
    }

    /**
     * @throws Throwable
     */
    public function disburse(VoucherRequest $request): JsonResponse
    {
        $response = VoucherRepository::disburse($request->input('enterprise_id'), $request->input('data'));

        return $this->successResponse($response);
    }
}
