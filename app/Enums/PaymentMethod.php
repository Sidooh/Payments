<?php

namespace App\Enums;

enum PaymentMethod
{
    case MPESA;
    case VOUCHER;
    case FLOAT;
    case SIDOOH_POINTS;
}
