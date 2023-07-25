<?php

namespace App\Enums;

enum MerchantType: string
{
    case MPESA_BUY_GOODS = 'MPESA_BUY_GOODS';
    case MPESA_PAY_BILL = 'MPESA_PAY_BILL';

    public function getTypeAndSubtype(): array
    {
        $type = PaymentType::tryFrom(config('services.sidooh.providers.mpesa.b2b')) ?? PaymentType::MPESA;
        return [$type, PaymentSubtype::B2B];
    }
}
