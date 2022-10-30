<?php

namespace App\Repositories\PaymentRepositories\Providers;

interface PaymentContract
{
    function requestPayment(): int;
}
