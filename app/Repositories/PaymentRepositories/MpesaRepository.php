<?php

namespace App\Repositories\PaymentRepositories;

use App\Repositories\PaymentRepositories\Providers\MpesaProvider;
use App\Repositories\PaymentRepositories\Providers\PaymentContract;

class MpesaRepository extends Repository
{
    public function getPaymentProvider(): PaymentContract
    {
        // TODO: Handle reference as needed
        return new MpesaProvider($this->paymentData->source, $this->paymentData->amount, $this->paymentData->reference);
    }
}
