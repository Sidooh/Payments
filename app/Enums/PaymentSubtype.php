<?php

namespace App\Enums;

enum PaymentSubtype: string
{
    case STK = 'STK';
    case C2B = 'C2B';
    case B2C = 'B2C';
    case CBA = 'CBA';
    case WALLET = 'WALLET';
    case VOUCHER = 'VOUCHER';
    case FLOAT = 'FLOAT';
    case BONUS = 'BONUS';
}
