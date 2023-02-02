<?php

namespace App\Enums;

enum Description: string
{
    case AIRTIME_PURCHASE = 'Airtime Purchase';
    case UTILITY_PURCHASE = 'Utility Purchase';

    case VOUCHER_DISBURSEMENT = 'Voucher Disbursement';
    case VOUCHER_REFUND = 'Voucher Refund';
    case VOUCHER_PURCHASE = 'Voucher Purchase';

    case FLOAT_PURCHASE = 'Float Purchase';

    case SUBSCRIPTION_PURCHASE = 'Subscription Purchase';
    case MERCHANT_PAYMENT = 'Merchant Payment';

    case PAYMENT_REVERSAL = 'Payment Reversal';

    case ACCOUNT_WITHDRAWAL = 'Account Withdrawal';
    case ACCOUNT_WITHDRAWAL_REFUND = 'Account Withdrawal Refund';
}
