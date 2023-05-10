<?php

namespace App\Enums;

enum PaymentType: string
{
    case MPESA = 'MPESA';
    case SIDOOH = 'SIDOOH';
    case TENDE = 'TENDE';
    case BUNI = 'BUNI';
}
