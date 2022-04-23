<?php

namespace App\Enums;

enum Description: string
{
    case VOUCHER_DISBURSEMENT = 'Voucher Disbursement';
    case VOUCHER_REFUND = 'Voucher Refund';
    case VOUCHER_PURCHASE = 'Voucher Purchase';
}
