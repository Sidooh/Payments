<?php

namespace App\Repositories\PaymentRepositories\Providers;

class MpesaProvider implements PaymentContract
{
    public function __construct(private readonly string $phone, private readonly int $amount, private readonly string $reference)
    {
    }

    function requestPayment(): int
    {
        $stkResponse = mpesa_request($this->phone, $this->amount, $this->reference);

        return $stkResponse->id;
    }
}
