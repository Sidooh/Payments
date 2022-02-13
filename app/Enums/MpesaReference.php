<?php

namespace App\Enums;

enum MpesaReference: string
{
    const AIRTIME = '1-AIRTIME';
    const PAY_SUBSCRIPTION = '2-SUBSCRIPTION';
    const PAY_VOUCHER = '3.2-VOUCHER';
    const PAY_UTILITY = '3.4-UTILITY';
    const PRE_AGENT_REGISTER_ASPIRING = '6.1-PRE-AGENT-ASPIRE';
    const PRE_AGENT_REGISTER_THRIVING = '6.2-PRE-AGENT';
    const AGENT_REGISTER_ASPIRING = '6.3-AGENT-ASPIRE';
    const AGENT_REGISTER_THRIVING = '6.4-AGENT';

    const AGENT_REGISTER = '6-AGENT';
}
