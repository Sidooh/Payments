<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Arr;
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
    public function getWithdrawalChargeAmount(int $amount): JsonResponse
    {
        $charges = config('services.sidooh.charges.withdrawal');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] > $amount && $ch['min'] <= $amount);

        if (! $charge) {
            return $this->errorResponse('Invalid amount.', 422);
        }

        return $this->successResponse($charge['charge']);
    }
}
