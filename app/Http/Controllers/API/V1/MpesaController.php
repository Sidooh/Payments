<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class MpesaController extends Controller
{
    public function getBySubType(string $subType = null): JsonResponse
    {
        $subType = match ($subType) {
            "stk" => PaymentSubtype::STK,
            "c2b" => PaymentSubtype::C2B,
            "b2c" => PaymentSubtype::B2C,
            default => null
        };

        $payments = Payment::whereType(PaymentType::MPESA->name)
            ->when($subType, fn(Builder $qry) => $qry->whereSubtype($subType->name))->with([
            "provider:id,status,reference,checkout_request_id,amount,phone,created_at",
            "provider.response:id,checkout_request_id,result_desc,created_at"
        ])->get();

        return $this->successResponse($payments);
    }
}
