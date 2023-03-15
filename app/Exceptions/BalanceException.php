<?php

namespace App\Exceptions;

use Exception;

class BalanceException extends Exception
{
    protected $code = 422;
}
