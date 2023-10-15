<?php

namespace App\Enums;

enum Initiator: string
{
    case CONSUMER = 'CONSUMER';
    case ENTERPRISE = 'ENTERPRISE';
    case MERCHANT = 'MERCHANT';
}
