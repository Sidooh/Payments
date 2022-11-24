<?php

namespace App\Enums;

enum PaymentType: string
{
    case MPESA = 'MPESA';
    case SIDOOH = 'SIDOOH';
    case TENDE = 'TENDE';
//    case BANK = 'BANK';
//    case PAYPAL = 'PAYPAL';
//    case OTHER = 'OTHER';
}
