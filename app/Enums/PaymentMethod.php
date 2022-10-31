<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MPESA = 'MPESA';
    case VOUCHER = 'VOUCHER';
    case FLOAT = 'FLOAT';

//    case SIDOOH_POINTS;


    function getTypeAndSubtype(): array
    {
        return match ($this) {
            self::MPESA => [PaymentType::MPESA, PaymentSubtype::STK],
            self::VOUCHER => [PaymentType::SIDOOH, PaymentSubtype::VOUCHER],
            self::FLOAT => [PaymentType::SIDOOH, PaymentSubtype::FLOAT],
        };
    }
}
