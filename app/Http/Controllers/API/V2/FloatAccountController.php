<?php

namespace App\Http\Controllers\API\V2;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountTopupRequest;
use App\Http\Resources\PaymentResource;
use App\Repositories\PaymentRepositories\PaymentRepository;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FloatAccountController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param FloatAccountTopupRequest $request
     * @return JsonResponse
     */
    public function credit(FloatAccountTopupRequest $request): JsonResponse
    {
        Log::info('...[CTRL - FLOAT_ACCOUNTv2]: Invoke...', $request->all());

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
                    PaymentSubtype::FLOAT,
                    ['float_account_id' => $request->float_account]
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
