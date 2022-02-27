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
    public function deposit(Request $request): Model|Builder|Voucher
    {
        $request->validate([
            'account_id' => ['required'],
            'amount'     => ['required'],
        ]);

        return VoucherRepository::deposit($request->input('account_id'), $request->input('amount'));
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
