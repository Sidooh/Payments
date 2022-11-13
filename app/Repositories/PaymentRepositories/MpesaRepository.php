<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\MpesaProvider;
use App\Repositories\PaymentRepositories\Providers\PaymentContract;

class MpesaRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        return new MpesaProvider($this->paymentData);
    }
}
