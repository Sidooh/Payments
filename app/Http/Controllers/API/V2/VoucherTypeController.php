<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisburseVoucherTypeRequest;
use App\Http\Requests\StoreVoucherTypeRequest;
use App\Models\Voucher;
use App\Models\VoucherType;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Repositories\SidoohRepositories\VoucherRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherTypeController extends Controller
{

    public function index(): JsonResponse
    {
        return $this->successResponse(VoucherType::latest()->get());
    }

    public function store(StoreVoucherTypeRequest $request): JsonResponse
    {
        $exists = VoucherType::whereName($request->name)->whereAccountId($request->account_id)->exists();
        if ($exists) {
            return $this->errorResponse("a similar voucher exists", 422);
        }

        $count = VoucherType::whereAccountId($request->account_id)->count();
        if ($count > 2) {
            return $this->errorResponse("limit of vouchers reached", 422);
        }

        $voucher = VoucherType::create([
            'name' => $request->name,
            'account_id' => $request->account_id,
        ]);

        return $this->successResponse($voucher);
    }

    public function show(VoucherType $voucherType, Request $request): JsonResponse
    {
        if ($request->boolean('with_vouchers')) {
            $voucherType->load(['vouchers' => function ($query) {
                $query->without('voucherType');
            }]);
        }

        return $this->successResponse($voucherType);
    }

    public function disburse(VoucherType $voucherType, DisburseVoucherTypeRequest $request): JsonResponse
    {
        Log::info('...[CTRL - VOUCHERTYPEv2]: Disburse...', $request->all());

        // TODO: Check voucher type is for account/ is authorized
        // TODO: Check float account is for account/ is authorized

        try {

//            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();


            $result = DB::transaction(function () use ($request, $voucherType) {
                $voucher = Voucher::whereVoucherTypeId($voucherType->id)
                    ->whereAccountId($request->account_id)
                    ->first();

                if (!$voucher) {
                    throw new Exception('invalid voucher selected', 422);
                }

                $fT = FloatAccountRepository::debit($request->source_account, $request->amount, $request->description);

                $vT = VoucherRepository::credit($voucher->id, $request->amount, $request->description);

                return [$fT, $vT];
            });

//            $repo = new PaymentRepository(
//                new PaymentDTO(
//                    $request->account_id,
//                    $request->amount,
//                    $type,
//                    $subtype,
//                    $request->description,
//                    $request->reference,
//                    $request->source_account,
//                    false,
//                    PaymentType::SIDOOH,
//                    PaymentSubtype::VOUCHER,
//                    ['voucher_id' => $request->voucher]
//                ),
//                $request->ipn
//            );

//            $payment = $repo->processPayment();

            return $this->successResponse($result, 'Disbursed.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process disbursement request.');
    }
}
