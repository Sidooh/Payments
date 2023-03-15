<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;

class ChargeController extends Controller
{
    public function getWithdrawalCharges(): JsonResponse
    {
        $charges = config('services.sidooh.charges.withdrawal');

        return $this->successResponse($charges);
    }

    /**
     * @throws \Exception
     */
    public function getWithdrawalCharge(int $amount): JsonResponse
    {
        try {
            $charge = withdrawal_charge($amount);

            return $this->successResponse($charge);
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }

    public function getPaybillCharges(): JsonResponse
    {
        $charges = config('services.sidooh.charges.paybill');

        return $this->successResponse($charges);
    }

    /**
     * @throws \Exception
     */
    public function getPaybillCharge(int $amount): JsonResponse
    {
        try {
            $charge = paybill_charge($amount);

            return $this->successResponse($charge);
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }
}
