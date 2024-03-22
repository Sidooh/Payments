<?php

namespace App\Enums;

enum PayableType
{
    case TRANSACTION;
    case PERSONAL_SAVING;
    case GROUP_SAVING;
}
