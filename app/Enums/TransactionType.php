<?php

namespace App\Enums;

enum TransactionType
{
    case PAYMENT;
    case WITHDRAWAL;
    case TRANSFER;

    case DEBIT;
    case CREDIT;
}
