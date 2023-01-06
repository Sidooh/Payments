<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\VoucherCreditRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Voucher;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Services\SidoohAccounts;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function show(Voucher $voucher, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('transactions', $relations)) {
            $voucher->load('transactions:id,voucher_id,type,amount,description,created_at')
                ->latest()
                ->limit(100);
        }

        if (in_array('account', $relations)) {
            $voucher->account = SidoohAccounts::find($voucher->account_id, true);
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

    /**
     * Handle the incoming request.
     *
     * @param VoucherCreditRequest $request
     * @return JsonResponse
     */
    public function credit(VoucherCreditRequest $request): JsonResponse
    {
        Log::info('...[CTRL - VOUCHER]: Credit...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    PaymentType::SIDOOH,
                    PaymentSubtype::VOUCHER,
                    ['voucher_id' => $request->voucher]
                ),
                $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment->refresh()), 'Payment Requested.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (MpesaException $e) {
            Log::critical($e);
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process credit request.');
    }
}
