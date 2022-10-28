<?php

namespace App\Http\Controllers\API\V2;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountTopupRequest;
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
