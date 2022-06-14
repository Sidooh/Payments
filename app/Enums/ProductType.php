<?php

namespace App\Enums;

enum ProductType: int
{
    case AIRTIME = 1;
    case SUBSCRIPTION = 2;
    case VOUCHER = 3;
    case MERCHANT = 4;
    case WITHDRAWAL = 5;
    case UTILITY = 6;
}
