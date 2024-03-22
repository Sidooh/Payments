<?php

namespace App\Enums;

enum Period: string
{
    case TODAY = 'today';
    case LAST_SEVEN_DAYS = 'last_7_days';
    case LAST_THIRTY_DAYS = 'last_30_days';
    case LAST_THREE_MONTHS = 'last_3_months';
    case LAST_SIX_MONTHS = 'last_6_months';
    case YTD = 'ytd';

    case MONTH = 'month';
    case YEAR = 'year';
}
