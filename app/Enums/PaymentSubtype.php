<?php

namespace App\Enums;

enum PaymentSubtype
{
    case STK;
    case C2B;
    case CBA;
    case WALLET;
    case VOUCHER;
    case FLOAT;
    case BONUS;
}
