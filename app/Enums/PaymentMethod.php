<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MPESA = 'MPESA';
    case VOUCHER = 'VOUCHER';
    case FLOAT = 'FLOAT';
//    case SIDOOH_POINTS;
}
