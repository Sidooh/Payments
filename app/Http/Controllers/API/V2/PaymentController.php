<?php

namespace App\Http\Controllers\API\V2;

use App\DTOs\PaymentDTO;
use App\Enums\MerchantType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantPaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param PaymentRequest $request
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function __invoke(PaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENTv2]: Invoke...', $request->all());

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
                    $request->source_account
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


    /**
     * Handle the incoming request.
     *
     * @param MerchantPaymentRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function merchant(MerchantPaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENTv2]: Merchant...', $request->all());

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

    /**
     * Handle the incoming request.
     *
     * @param WithdrawalRequest $request
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function withdraw(WithdrawalRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT ~v2]: Withdraw...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$type2, $subtype2] = PaymentMethod::from($request->destination)->getWithdrawalTypeAndSubtype();
            $subtype2 = $type2 === PaymentType::MPESA ?  PaymentSubtype::B2C: $subtype2;

            $destination = match ($subtype2) {
                PaymentSubtype::VOUCHER => 'voucher_id',
                PaymentSubtype::B2C => 'phone',
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

    /**
     * Handle the incoming request.
     *
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        // TODO: Add auth check functionality for this

        return $this->successResponse(PaymentResource::make($payment), 'Payment query.');
    }
}
