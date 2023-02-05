<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Services\SidoohAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $vouchers = Voucher::latest();

        if ($id = $request->integer('account_id')) {
            $vouchers->whereAccountId($id);
        }

        if (in_array('transactions', $relations)) {
            $vouchers->with('transactions:id,voucher_id,type,amount,description,created_at')
                ->latest()
                ->limit(10);
        }

        $vouchers = $vouchers->limit(1000)->get();

        if (in_array('account', $relations)) {
            $vouchers = withRelation('account', $vouchers, 'account_id', 'id');
        }

        return $this->successResponse($vouchers);
    }

    /**
     * @throws \Exception
     */
    public function show(Voucher $voucher, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('transactions', $relations)) {
            $voucher->load('transactions:id,voucher_id,type,amount,description,created_at')
                ->latest()
                ->limit(100);
        }

        if (in_array('account', $relations)) {
            $voucher->account = SidoohAccounts::find($voucher->account_id);
        }

        return $this->successResponse($voucher->toArray());
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        $voucher = Voucher::firstOrCreate([
            'account_id'      => $request->account_id,
            'voucher_type_id' => $request->voucher_type_id,
        ]);

        return $this->successResponse($voucher);
    }
}
