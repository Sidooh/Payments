<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use DrH\Mpesa\Library\MpesaAccount;
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

        $payBillSwitch = config('services.sidooh.payment_providers.mpesa.pay_bill_switch_amount');
        if ($payBillSwitch > 0 && $this->paymentDTO->amount > $payBillSwitch) {
//            if TILL add partyB and type to MpesaAccount::TILL
            $mpesaAcc = new MpesaAccount(
                config('services.sidooh.payment_providers.mpesa.pay_bill.shortcode'),
                config('services.sidooh.payment_providers.mpesa.pay_bill.key'),
                config('services.sidooh.payment_providers.mpesa.pay_bill.secret'),
                config('services.sidooh.payment_providers.mpesa.pay_bill.passkey')
            );
        } else {
            $mpesaAcc = null;
        }

        // TODO: Add float option as well
        return match ($this->paymentDTO->subtype) {
            PaymentSubtype::STK => mpesa_request($this->paymentDTO->source, $this->paymentDTO->amount, null, null, $mpesaAcc)->id,
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
