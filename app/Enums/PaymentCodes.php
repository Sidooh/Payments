<?php

namespace App\Enums;

enum PaymentCodes: int
{
    // MPESA ERRORS
    case MPESA_FAILED = 100;
    case MPESA_INSUFFICIENT_BALANCE = 101;
    case MPESA_CANCELLED = 102;
    case MPESA_TIMEOUT = 103;

}
