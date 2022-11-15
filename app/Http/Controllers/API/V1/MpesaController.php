<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class MpesaController extends Controller
{
    public function getSTKPayments(): JsonResponse
    {
        $payments = Payment::whereType(PaymentType::MPESA->name)->whereSubtype(PaymentSubtype::STK->name)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->get();

        return $this->successResponse($payments);
    }

    public function getC2BPayments(): JsonResponse
    {
        $payments = Payment::whereType(PaymentType::MPESA->name)->whereSubtype(PaymentSubtype::C2B->name)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->get();

        return $this->successResponse($payments);
    }

    public function getB2CPayments(): JsonResponse
    {
        $payments = Payment::whereType(PaymentType::SIDOOH->name)->whereSubtype(PaymentSubtype::FLOAT)->latest()->get();

        return $this->successResponse($payments);
    }
}
