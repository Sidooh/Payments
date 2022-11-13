<?php

namespace App\DTOs;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Services\SidoohAccounts;
use Exception;

class PaymentDTO
{
    public Payment $payment;

    /**
     * @throws Exception
     */
    public function __construct(
        public readonly int             $accountId,
        public readonly int             $amount,
        public readonly PaymentType     $type,
        public readonly PaymentSubtype  $subtype,
        public readonly string          $description,
        public readonly string          $reference,
        public readonly int             $source,
        public bool                     $isWithdrawal = false,
        public readonly ?PaymentType    $destinationType = null,
        public readonly ?PaymentSubtype $destinationSubtype = null,
        public readonly ?array          $destinationData = null,
    )
    {
        $this->validate();
    }

    /**
     * @throws Exception
     */
    function validate(): void
    {
        SidoohAccounts::find($this->accountId);

        $validPaymentCombinations = match ($this->subtype) {
            PaymentSubtype::STK => [null, PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT, PaymentSubtype::B2B],
            PaymentSubtype::VOUCHER => [null, PaymentSubtype::VOUCHER, PaymentSubtype::B2B],
            PaymentSubtype::FLOAT => [PaymentSubtype::VOUCHER, PaymentSubtype::B2C],
            default => throw new Exception('Unsupported payment combination')
        };

        if (!in_array($this->destinationSubtype, $validPaymentCombinations)) {
            throw new Exception('Unsupported payment combination');
        }
    }

    /**
     * @throws Exception
     */
    static function fromPayment(Payment $payment): PaymentDTO
    {
        $dto = new PaymentDTO(
            $payment->account_id,
            $payment->amount,
            PaymentType::tryFrom($payment->type),
            PaymentSubtype::tryFrom($payment->subtype),
            $payment->description,
            $payment->reference,
            $payment->provider_id,
            true,
            PaymentType::tryFrom($payment->destination_type),
            PaymentSubtype::tryFrom($payment->destination_subtype),
            $payment->destination_data
        );
        $dto->payment = $payment;

        return $dto;
    }
}
