<?php

namespace App\Repositories\PaymentRepositories\Providers;

interface PaymentContract
{
    public function requestPayment(): int;
}
