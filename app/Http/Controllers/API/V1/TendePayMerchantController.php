<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\TendePayMerchant;
use Cache;
use Illuminate\Http\JsonResponse;

class TendePayMerchantController extends Controller
{
    public function searchMerchant(int $code): JsonResponse
    {
        $merchant = Cache::get("merchant_$code");

        if (!$merchant) {
            if (is_blacklisted_merchant($code)) {
                return $this->errorResponse('invalid merchant', 422);
            }

            $merchant = TendePayMerchant::select('code', 'name')->whereCode($code)->firstOrFail();

            Cache::forever("merchant_$code", $merchant);
        }

        return $this->successResponse($merchant);
    }
}
