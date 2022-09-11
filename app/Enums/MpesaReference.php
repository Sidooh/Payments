<?php

namespace App\Enums;

enum MpesaReference: string
{
    const AIRTIME = 'AIRTIME';
    const PAY_VOUCHER = 'VOUCHER';
    const PAY_UTILITY = 'UTILITY';
    const SUBSCRIPTION = 'SUBSCRIPTION';

    const FLOAT = 'FLOAT';
}
