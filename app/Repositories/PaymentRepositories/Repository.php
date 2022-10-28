<?php

namespace App\Repositories\PaymentRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentType;
use Exception;

class Repository
{
    private PaymentRepository $sourceRepository;
    private PaymentRepository $destinationRepository;

    /**
     * @throws Exception
     */
    public function __construct(private readonly PaymentDTO $paymentData)
    {
        $this->setPaymentRepositories();
    }

    function setPaymentRepositories(): Repository
    {
        $this->sourceRepository = match ($this->paymentData->type) {
            PaymentType::MPESA => new MpesaRepository(),
            default => throw new Exception('Unsupported source')
        };

        $this->destinationRepository = match ($this->paymentData->destinationType) {
            PaymentType::SIDOOH => new SidoohRepository(),
            default => throw new Exception('Unsupported destination')
        };

        return $this;
    }

    function getSourcePaymentRepository(): PaymentRepository
    {
        return $this->sourceRepository;
    }

    function getDestinationPaymentRepository(): PaymentRepository
    {
        return $this->destinationRepository;
    }

    function process(): void
    {

    }
}
