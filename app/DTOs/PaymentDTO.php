<?php

namespace App\DTOs;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Models\FloatAccount;
use App\Models\Payment;
use App\Services\SidoohAccounts;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentDTO
{
    public Payment $payment;

    /**
     * @throws Exception
     */
    public function __construct(
        public readonly int $accountId,
        public readonly int $amount,
        public readonly PaymentType $type,
        public readonly PaymentSubtype $subtype,
        public readonly string $description,
        public readonly ?string $reference,
        public readonly int $source,
        public bool $isWithdrawal = false,
        public readonly ?PaymentType $destinationType = null,
        public readonly ?PaymentSubtype $destinationSubtype = null,
        public readonly ?array $destinationData = null,
        public readonly int $charge = 0,
    ) {
        $this->validate();
    }

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        SidoohAccounts::find($this->accountId);

        $validPaymentCombinations = match ($this->subtype) {
            PaymentSubtype::STK, PaymentSubtype::VOUCHER => [null, PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT, PaymentSubtype::B2B],
            PaymentSubtype::FLOAT => [PaymentSubtype::VOUCHER, PaymentSubtype::B2C, PaymentSubtype::B2B, PaymentSubtype::FLOAT],
            default               => throw new HttpException(422, 'Unsupported payment source')
        };

        if (! in_array($this->destinationSubtype, $validPaymentCombinations)) {
            throw new HttpException(422, 'Unsupported payment destination');
        }

        if ($this->destinationSubtype === PaymentSubtype::FLOAT) {
            $exists = FloatAccount::whereId($this->destinationData['float_account_id'])->exists();
            if (! $exists) {
                throw new HttpException(422, 'Invalid float account');
            }

            $isSameAsSource = $this->subtype === PaymentSubtype::FLOAT && $this->source == $this->destinationData['float_account_id'];
            if ($isSameAsSource) {
                throw new HttpException(422, 'Invalid float account');
            }
        }
    }

    public function totalAmount(): int
    {
        return $this->amount + $this->charge;
    }

    /**
     * @throws Exception
     */
    public static function fromPayment(Payment $payment): PaymentDTO
    {
        $dto = new PaymentDTO(
            $payment->account_id,
            $payment->amount,
            $payment->type,
            $payment->subtype,
            $payment->description,
            $payment->reference,
            $payment->provider_id,
            true,
            $payment->destination_type,
            $payment->destination_subtype,
            $payment->destination_data,
            $payment->charge
        );
        $dto->payment = $payment;

        return $dto;
    }
}
