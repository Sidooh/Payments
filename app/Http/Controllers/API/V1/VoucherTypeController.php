<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisburseVoucherTypeRequest;
use App\Http\Requests\StoreVoucherTypeRequest;
use App\Models\Voucher;
use App\Models\VoucherType;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Repositories\SidoohRepositories\VoucherRepository;
use App\Services\SidoohAccounts;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VoucherTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $types = VoucherType::latest();

        if ($id = $request->integer('account_id')) {
            $types->whereAccountId($id);
        }

        if (in_array('vouchers', $relations)) {
            $types->with('vouchers:id,account_id,voucher_type_id,balance')
                ->latest()
                ->limit(10);
        }

        $types = $types->limit(1000)->get();

        if (in_array('account', $relations)) {
            $types = withRelation('account', $types, 'account_id', 'id');
        }

        return $this->successResponse($types);
    }

    public function store(StoreVoucherTypeRequest $request): JsonResponse
    {
        $exists = VoucherType::whereName($request->name)->whereAccountId($request->account_id)->exists();
        if ($exists) {
            return $this->errorResponse('a similar voucher exists', 422);
        }

        $count = VoucherType::whereAccountId($request->account_id)->count();
        if ($count > 2) {
            return $this->errorResponse('limit of vouchers reached', 422);
        }

        $voucher = VoucherType::create([
            'name'       => $request->name,
            'account_id' => $request->account_id,
        ]);

        return $this->successResponse($voucher);
    }

    public function show(VoucherType $voucherType, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('vouchers', $relations)) {
            $voucherType->load(['vouchers' => function($query) {
                $query->without('voucherType');
            }]);
        }

        if (in_array('account', $relations)) {
            $voucherType->account = SidoohAccounts::find($voucherType->account_id);
        }

        return $this->successResponse($voucherType);
    }

    public function disburse(VoucherType $voucherType, DisburseVoucherTypeRequest $request): JsonResponse
    {
        Log::info('...[CTRL - VOUCHER TYPE]: Disburse...', $request->all());

        // TODO: Check voucher type is for account/ is authorized
        // TODO: Check float account is for account/ is authorized

        try {
            $result = DB::transaction(function() use ($request, $voucherType) {
                $voucher = Voucher::whereVoucherTypeId($voucherType->id)
                    ->whereAccountId($request->account_id)
                    ->first();

                if (! $voucher) {
                    throw new Exception('invalid voucher selected', 422);
                }

                $fT = FloatAccountRepository::debit($request->source_account, $request->amount, $request->description);

                $vT = VoucherRepository::credit($voucher->id, $request->amount, $request->description);

                return [$fT, $vT];
            }, 2);

            return $this->successResponse($result, 'Disbursed.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (Exception|Throwable $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process disbursement request.');
    }
}
