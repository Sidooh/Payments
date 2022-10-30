<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\PaymentContract;
use App\Repositories\PaymentRepositories\Providers\SidoohProvider;

class SidoohRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        // TODO: Handle reference as needed
        return new SidoohProvider($this->paymentData);
    }
}
