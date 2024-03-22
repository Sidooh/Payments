<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\BuniProvider;
use App\Repositories\PaymentRepositories\Providers\PaymentContract;

class BuniRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        return new BuniProvider($this->paymentData);
    }
}
