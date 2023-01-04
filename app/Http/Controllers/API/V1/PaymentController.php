<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\PaymentDTO;
use App\Enums\MerchantType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantPaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use DrH\Mpesa\Exceptions\MpesaException;
use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::latest();

        if ($request->has('status') && $status = Status::tryFrom($request->status)) {
            $payments->whereStatus($status);
        }

        $payments = $payments->limit(1000)->get();

        return $this->successResponse($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        // TODO: Add auth check functionality for this

        if ($payment->subtype === PaymentSubtype::STK->name) {
            $payment->load([
                'provider:id,status,reference,description,checkout_request_id,amount,phone,created_at',
                'provider.response:id,checkout_request_id,mpesa_receipt_number,phone,result_desc,created_at',
            ]);
        }

        if ($payment->subtype === PaymentSubtype::VOUCHER->name) {
            $payment->load([
                'provider:id,type,amount,description,created_at',
            ]);
        }

        // TODO: Confirm columns for all the below subtypes

        if ($payment->subtype === PaymentSubtype::C2B->name) {
            $payment->load([
                'provider',
            ]);
        }

        if ($payment->destination_subtype === PaymentSubtype::FLOAT->name) {
            $payment->load([
                'destinationProvider:id,type,amount,description,created_at',
            ]);
        }

        if ($payment->destination_subtype === PaymentSubtype::VOUCHER->name) {
            $payment->load([
                'destinationProvider:id,type,amount,description,created_at',
            ]);
        }

        if ($payment->destination_subtype === PaymentSubtype::B2C->name) {
            $payment->load([
                'destinationProvider.response.parameter',
            ]);
        }

        if ($payment->destination_subtype === PaymentSubtype::B2B->name) {
            $payment->load([
                'destinationProvider.callback',
            ]);
        }

        return $this->successResponse($payment);
    }

    public function __invoke(PaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Invoke...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$type2, $subtype2] = PaymentMethod::from($request->destination)->getTypeAndSubtype();

            $destinationData = match ($subtype2) {
                PaymentSubtype::FLOAT   => 'float_account_id',
                PaymentSubtype::VOUCHER => 'voucher_id',
                default                 => throw new HttpException(422, 'Only float account and voucher are supported for destination.')
            };

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
                    $type2,
                    $subtype2,
                    [$destinationData => $request->destination_account],
                ),
                $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (MpesaException $e) {
            Log::critical($e);
        } catch (HttpException $err) {
            Log::error($err);

            return $this->errorResponse($err->getMessage(), $err->getStatusCode());
        } catch (Exception|Throwable|Error $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function merchant(MerchantPaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Merchant...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            $merchantType = MerchantType::from($request->merchant_type);
            [$type2, $subtype2] = $merchantType->getTypeAndSubtype();

            $destination = $merchantType === MerchantType::MPESA_PAY_BILL ?
                $request->only('merchant_type', 'paybill_number', 'account_number') :
                $request->only('merchant_type', 'till_number', 'account_number');

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
                    $type2,
                    $subtype2,
                    $destination
                ),
                $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (MpesaException $e) {
            Log::critical($e);
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function withdraw(WithdrawalRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Withdraw...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$type2, $subtype2] = PaymentMethod::from($request->destination)->getWithdrawalTypeAndSubtype();
            $subtype2 = $type2 === PaymentType::MPESA ? PaymentSubtype::B2C : $subtype2;

            $destination = match ($subtype2) {
                PaymentSubtype::VOUCHER => 'voucher_id',
                PaymentSubtype::B2C     => 'phone',
            };

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
                    $type2,
                    $subtype2,
                    [$destination => $request->destination_account]
                ),
                $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Withdrawal Requested.');
            // TODO: Change to PaymentException - create one and use internally
        } catch (MpesaException $e) {
            Log::critical($e);
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function typeAndSubtype(string $type, string $subType): JsonResponse
    {
        return match (PaymentType::tryFrom(strtoupper($type))) {
            PaymentType::SIDOOH => match (PaymentSubtype::tryFrom(strtoupper($subType))) {
                PaymentSubtype::B2B => $this->getB2BPayments(),
                default             => throw new HttpException(422, "Unexpected sub-type $subType for type $type")
            },
            PaymentType::MPESA => match (PaymentSubtype::tryFrom(strtoupper($subType))) {
                PaymentSubtype::B2C => $this->getB2CPayments(),
                PaymentSubtype::STK => $this->getSTKPayments(),
                PaymentSubtype::C2B => $this->getC2BPayments(),
                default             => throw new HttpException(422, "Unexpected sub-type $subType for type $type"),
            },
            default => throw new HttpException(422, "Unexpected payment type $type")
        };
    }

    public function getSTKPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::STK->name)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getC2BPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::C2B)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getB2CPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationSubtype(PaymentSubtype::B2C)->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getB2BPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationType(PaymentType::TENDE)->whereDestinationSubtype(PaymentSubtype::B2B)
            ->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function queryMpesaStatus(): JsonResponse
    {
        $exitCode = Artisan::call('mpesa:query_stk_status');

        return $this->successResponse(['Status' => $exitCode]);
    }
}
