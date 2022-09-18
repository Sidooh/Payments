<?php

namespace App\Enums;

enum Initiator: string
{
    case CONSUMER = 'CONSUMER';
    case AGENT = 'AGENT';
    case ENTERPRISE = "ENTERPRISE";
}
