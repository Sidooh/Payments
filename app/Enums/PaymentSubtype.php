<?php

namespace App\Enums;

enum PaymentSubtype: string
{
    case STK = 'STK';
    case C2B = 'C2B';
    case B2C = 'B2C';
    case B2B = 'B2B';
    case VOUCHER = 'VOUCHER';
    case FLOAT = 'FLOAT';
}
