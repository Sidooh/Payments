<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\Description;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Repositories\SidoohRepositories\VoucherRepository;
use Exception;
use Throwable;

class SidoohProvider implements PaymentContract
{
    public function __construct(private readonly PaymentDTO $paymentDTO)
    {
    }

    /**
     * @throws Throwable
     */
    public function requestPayment(): int
    {
        return match ($this->paymentDTO->isWithdrawal) {
            false => $this->requestSourcePayment(),
            true  => $this->requestDestinationPayment()
        };
    }

    /**
     * @throws Throwable
     */
    private function requestSourcePayment(): int
    {
        if ($this->paymentDTO->type !== PaymentType::SIDOOH) {
            throw new Exception('Unsupported payment type');
        }

        // TODO: Add float option as well
        return match ($this->paymentDTO->subtype) {
            PaymentSubtype::VOUCHER => VoucherRepository::debit($this->paymentDTO->source, $this->paymentDTO->totalAmount(), $this->paymentDTO->description)->id,
            PaymentSubtype::FLOAT   => FloatAccountRepository::debit($this->paymentDTO->source, $this->paymentDTO->amount, $this->paymentDTO->description, $this->paymentDTO->charge)->id,
            default                 => throw new Exception('Unsupported payment subtype')
        };
    }

    /**
     * @throws Throwable
     */
    private function requestDestinationPayment(): int
    {
        if ($this->paymentDTO->destinationType !== PaymentType::SIDOOH) {
            throw new Exception('Unsupported payment type');
        }

        return match ($this->paymentDTO->destinationSubtype) {
            PaymentSubtype::VOUCHER => $this->voucher(),
            PaymentSubtype::FLOAT   => $this->float(),
            default                 => throw new Exception('Unsupported payment subtype')
        };
    }

    /**
     * @throws \Throwable
     */
    private function voucher(): int
    {
        $voucherId = $this->paymentDTO->destinationData['voucher_id'];
        $description = $this->paymentDTO->description ?? Description::VOUCHER_PURCHASE->value;
        $transaction = VoucherRepository::credit($voucherId, $this->paymentDTO->amount, $description);

        return $transaction->id;
    }

    /**
     * @throws \Throwable
     */
    private function float(): int
    {
        $accountId = $this->paymentDTO->destinationData['float_account_id'];
        $description = $this->paymentDTO->description ?? Description::FLOAT_PURCHASE->value;
        $transaction = FloatAccountRepository::credit($accountId, $this->paymentDTO->amount, $description);

        return $transaction->id;
    }
}
