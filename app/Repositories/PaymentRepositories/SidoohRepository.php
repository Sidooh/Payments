<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\PaymentContract;
use App\Repositories\PaymentRepositories\Providers\SidoohProvider;

class SidoohRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        return new SidoohProvider($this->paymentData);
    }
}
