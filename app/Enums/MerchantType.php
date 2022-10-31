<?php

namespace App\Enums;

enum MerchantType: string
{
    case MPESA_BUY_GOODS = 'MPESA_BUY_GOODS';
    case MPESA_PAY_BILL = 'MPESA_PAY_BILL';

    function getTypeAndSubtype(): array
    {
        return [PaymentType::SIDOOH, PaymentSubtype::B2B];
    }
}
