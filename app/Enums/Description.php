<?php

namespace App\Enums;

enum Description: string
{
    case AIRTIME_PURCHASE = 'Airtime Purchase';
    case UTILITY_PURCHASE = 'Utility Purchase';

    case VOUCHER_DISBURSEMENT = 'Voucher Disbursement';
    case VOUCHER_REFUND = 'Voucher Refund';
    case VOUCHER_PURCHASE = 'Voucher Purchase';

    case SUBSCRIPTION_PURCHASE = "Subscription Purchase";
}
