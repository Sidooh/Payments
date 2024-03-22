<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use DrH\Buni\Facades\BuniStk;
use Exception;

class BuniProvider implements PaymentContract
{
    public function __construct(private PaymentDTO $paymentDTO)
    {
    }

    /**
     * @throws Exception
     */
    public function requestPayment(): int
    {
        return match ($this->paymentDTO->isWithdrawal) {
            false => $this->requestSourcePayment(),
            true => throw new Exception('Provider does not support destination payments')
        };
    }

    /**
     * @throws Exception
     */
    private function requestSourcePayment(): int
    {
        if ($this->paymentDTO->type !== PaymentType::BUNI) {
            throw new Exception('Unsupported payment type');
        }

        $amount = $this->paymentDTO->totalAmount();

        $reference = config('services.sidooh.providers.buni.till') . '#SIDOOH';

        return match ($this->paymentDTO->subtype) {
            PaymentSubtype::STK => BuniStk::push($amount, $this->paymentDTO->source, $reference, $this->paymentDTO->description)->id,
            default => throw new Exception('Unsupported payment subtype')
        };
    }

}
