<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    protected $code = 500;
}
