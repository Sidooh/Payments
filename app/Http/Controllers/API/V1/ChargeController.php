<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;

class ChargeController extends Controller
{
    public function getWithdrawalCharges(): JsonResponse
    {
        return $this->successResponse(config('services.sidooh.charges.withdrawal'));
    }

    /**
     * @throws Exception
     */
    public function getWithdrawalCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(withdrawal_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }

    public function getPayBillCharges(): JsonResponse
    {
        return $this->successResponse(config('services.sidooh.charges.pay_bill'));
    }

    /**
     * @throws Exception
     */
    public function getPayBillCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(pay_bill_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }

    public function getBuyGoodsCharges(): JsonResponse
    {
        return $this->successResponse(config('services.sidooh.charges.buy_goods'));
    }

    /**
     * @throws Exception
     */
    public function getBuyGoodsCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(buy_goods_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }


    public function getMpesaFloatCharges(): JsonResponse
    {
        return $this->successResponse(config('services.sidooh.charges.mpesa_float'));
    }

    /**
     * @throws Exception
     */
    public function getMpesaFloatCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(mpesa_float_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }

    public function getMpesaWithdrawalCharges(): JsonResponse
    {
        return $this->successResponse(config('services.sidooh.charges.mpesa_withdrawal'));
    }

    /**
     * @throws Exception
     */
    public function getMpesaWithdrawalCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(mpesa_withdrawal_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }

    /**
     * @throws Exception
     */
    public function getMpesaCollectionCharge(int $amount): JsonResponse
    {
        try {
            return $this->successResponse(mpesa_collection_charge($amount));
        } catch (Exception) {
            return $this->errorResponse('Invalid amount.', 422);
        }
    }
}
