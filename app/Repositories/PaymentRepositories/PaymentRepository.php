<?php

namespace App\Repositories\PaymentRepositories;

interface PaymentRepository
{
    function requestPayment(): int;
}
