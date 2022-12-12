<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use Exception;

class MpesaProvider implements PaymentContract
{
    public function __construct(private readonly PaymentDTO $paymentDTO)
    {
    }

    /**
     * @throws Exception
     */
    public function requestPayment(): int
    {
        return match ($this->paymentDTO->isWithdrawal) {
            false => $this->requestSourcePayment(),
            true  => $this->requestDestinationPayment()
        };
    }

    /**
     * @throws Exception
     */
    private function requestSourcePayment(): int
    {
        if ($this->paymentDTO->type !== PaymentType::MPESA) {
            throw new Exception('Unsupported payment type');
        }

        // TODO: Add float option as well
        return match ($this->paymentDTO->subtype) {
            PaymentSubtype::STK => mpesa_request($this->paymentDTO->source, $this->paymentDTO->amount, $this->paymentDTO->reference)->id,
            default             => throw new Exception('Unsupported payment subtype')
        };
    }

    /**
     * @throws Exception
     */
    private function requestDestinationPayment(): int
    {
        if ($this->paymentDTO->destinationType !== PaymentType::MPESA) {
            throw new Exception('Unsupported payment type');
        }

        $phone = $this->paymentDTO->destinationData['phone'];

        return match ($this->paymentDTO->destinationSubtype) {
            PaymentSubtype::B2C => mpesa_send($phone, $this->paymentDTO->amount, 'payment')->id,
            default             => throw new Exception('Unsupported payment subtype')
        };
    }
}
