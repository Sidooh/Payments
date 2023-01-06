<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\PaymentContract;
use App\Repositories\PaymentRepositories\Providers\TendeProvider;

class TendeRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        return new TendeProvider($this->paymentData);
    }
}
