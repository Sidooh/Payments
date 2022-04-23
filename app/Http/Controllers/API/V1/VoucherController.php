<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\Voucher;
use App\Repositories\VoucherRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VoucherController extends Controller
{
    public function show(Voucher $voucher): array
    {
        return $voucher->toArray();
    }

    public function getAccountVouchers(int $accountId): array
    {
        $vouchers = Voucher::whereAccountId($accountId)->get();

        return $vouchers->toArray();
    }

    public function credit(Request $request): Model|Builder|Voucher
    {
        $request->validate([
            'account_id'  => ['required'],
            'amount'      => ['required'],
            "description" => ["required", "string"],
            "notify"      => ['required', 'boolean']
        ]);

        $accountId = $request->input("account_id");
        $amount = $request->input("amount");
        $description = $request->input("description");
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
