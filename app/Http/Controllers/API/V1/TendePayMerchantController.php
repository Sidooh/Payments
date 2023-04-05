<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\TendePayMerchant;
use Illuminate\Http\JsonResponse;

class TendePayMerchantController extends Controller
{
    //
    public function searchMerchant(int $code): JsonResponse
    {
        $merchant = TendePayMerchant::select('code', 'name')->whereCode($code)->firstOrFail();

        return $this->successResponse($merchant);
    }
}
