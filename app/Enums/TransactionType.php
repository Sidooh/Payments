<?php

namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT = 'PAYMENT';
    case WITHDRAWAL = 'WITHDRAWAL';
    case TRANSFER = 'TRANSFER';

    case DEBIT = 'DEBIT';
    case CREDIT = 'CREDIT';
}
