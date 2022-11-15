<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class MpesaController extends Controller
{
    /**
     * @throws \Exception
     */
    public function __invoke(string $type, string $subType): JsonResponse
    {
        return match (PaymentType::tryFrom(strtoupper($type))) {
            PaymentType::SIDOOH => throw new \Exception('To be implemented'),
            PaymentType::MPESA  => match (PaymentSubtype::tryFrom(strtoupper($subType))) {
                PaymentSubtype::B2C => $this->getB2CPayments(),
                PaymentSubtype::STK => $this->getSTKPayments(),
                PaymentSubtype::C2B => $this->getC2BPayments(),
                default             => throw new \Exception("Unexpected sub-type $subType for type $type"),
            },
            default => throw new \Exception("Unexpected payment type $type")
        };
    }

    public function getSTKPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::STK->name)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->get();

        return $this->successResponse($payments);
    }

    public function getC2BPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::C2B)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->get();

        return $this->successResponse($payments);
    }

    public function getB2CPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationSubtype(PaymentSubtype::B2C)->latest()->get();

        return $this->successResponse($payments);
    }

    public function getB2BPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationType(PaymentType::TENDE)->whereDestinationSubtype(PaymentSubtype::B2B)
            ->latest()->get();

        return $this->successResponse($payments);
    }
}
