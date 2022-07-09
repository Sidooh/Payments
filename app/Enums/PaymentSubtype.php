<?php

namespace App\Enums;

enum PaymentSubtype
{
    case STK;
    case C2B;
    case B2C;
    case CBA;
    case WALLET;
    case VOUCHER;
    case FLOAT;
    case BONUS;
}
