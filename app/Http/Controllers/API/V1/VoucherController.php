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

    public function credit(Request $request): Model|Builder|Voucher
    {
        $request->validate([
            'account_id' => ['required'],
            'amount'     => ['required'],
            "notify"     => ['required', 'boolean']
        ]);

        $accountId = $request->input("account_id");
        $amount = $request->input("amount");
        $notify = $request->boolean("notify");

        return VoucherRepository::credit($accountId, $amount, $notify);
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
