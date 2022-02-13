<?php

namespace App\Enums;

enum PaymentType
{
    case MOBILE;
    case MPESA;
    case SIDOOH;
    case BANK;
    case PAYPAL;
    case OTHER;
}
