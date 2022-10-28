<?php

namespace App\Http\Controllers\API\V2;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantPaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Repositories\PaymentRepositories\Repository;
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

        [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
        $repo = new Repository(new PaymentDTO(
            $request->accountId,
            $request->amount,
            $type,
            $subtype,
            $request->description,
            $request->reference,
        ));

        $transactions = collect($request->transactions);

        $repo = new PaymentRepository($transactions, PaymentMethod::from($request->payment_mode), $request->debit_account);

        try {
            $data = $repo->process();

            return $this->successResponse($data, 'Payment Created.');
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
     */
    public function merchant(MerchantPaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENTv2]: Invoke...', $request->all());

        $transactions = collect($request->transactions);

        $repo = new PaymentRepository($transactions, PaymentMethod::from($request->payment_mode), $request->debit_account);

        try {
            $data = $repo->process();

            return $this->successResponse($data, 'Payment Created.');
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
}
