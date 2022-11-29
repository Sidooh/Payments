<?php

namespace App\Repositories\PaymentRepositories;

use App\DTOs\PaymentDTO;
use App\Repositories\PaymentRepositories\Providers\PaymentContract;

abstract class Repository
{
    abstract protected function getPaymentProvider(): PaymentContract;

    public function __construct(protected readonly PaymentDTO $paymentData)
    {
    }

    public function process(): int
    {
        $provider = $this->getPaymentProvider();

        return $provider->requestPayment();
    }
}
