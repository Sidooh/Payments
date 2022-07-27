<?php

namespace App\Enums;

enum ProductType: int
{
    case AIRTIME = 1;
    case SUBSCRIPTION = 2;
    case VOUCHER = 3;
    case WITHDRAWAL = 4;
    case UTILITY = 5;
    case MERCHANT = 6;
}
