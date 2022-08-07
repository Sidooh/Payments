<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Description;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Repositories\VoucherRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(",", $request->query("with"));

        $vouchers = Voucher::latest();

        if(in_array("voucher_transactions", $relations)) {
            $vouchers = $vouchers->with("voucherTransactions:id,voucher_id,type,amount,description,created_at")
                ->limit(50);
        }

        $vouchers = $vouchers->get();

        if(in_array("account", $relations)) {
            $vouchers = withRelation("account", $vouchers, "account_id", "id");
        }

        return $this->successResponse($vouchers);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $relations = explode(",", $request->query("with"));

        $transactions = VoucherTransaction::query();

        if(in_array("voucher", $relations)) {
            $transactions = $transactions->with("voucher:id,account_id,type,balance");
        }

        if(in_array("payment", $relations)) {
            $transactions = $transactions->with("payment:id,provider_id,subtype,status");
        }

        $transactions->orderBy('id', 'desc')->limit(100);

        return $this->successResponse($transactions->get());
    }

    public function show(Voucher $voucher): JsonResponse
    {
        return $this->successResponse($voucher->toArray());
    }

    public function getAccountVouchers(int $accountId): JsonResponse
    {
        $vouchers = Voucher::select(["id", "type", "balance"])->whereAccountId($accountId)->get();

        if($vouchers->isEmpty()) {
            $vouchers = [Voucher::create(["account_id" => $accountId, "type" => VoucherType::SIDOOH])];
        }

        return $this->successResponse($vouchers);
    }

    public function credit(Request $request): JsonResponse
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

        $response = VoucherRepository::credit($accountId, $amount, $description);

        return $this->successResponse($response);
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
