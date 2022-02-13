<?php

namespace App\Enums;

enum Status
{
    case COMPLETED;
    case FAILED;
    case PENDING;
    case REIMBURSED;
}
