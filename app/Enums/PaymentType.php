<?php

namespace App\Enums;

enum PaymentType: string
{
    case MOBILE = 'MOBILE';
    case MPESA = 'MPESA';
    case SIDOOH = 'SIDOOH';
    case BANK = 'BANK';
    case PAYPAL = 'PAYPAL';
    case OTHER = 'OTHER';
}
