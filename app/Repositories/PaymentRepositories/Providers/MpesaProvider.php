<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\MerchantType;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Services\SidoohAccounts;
use DrH\Mpesa\Library\B2BPayment;
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
            true => $this->requestDestinationPayment()
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

        $reference = null;

        $amount = $this->paymentDTO->totalAmount();
        $payBillSwitch = config('services.sidooh.providers.mpesa.pay_bill_switch_amount');
        if ($payBillSwitch > 0 && $amount > $payBillSwitch) {
//            if TILL add partyB and type to MpesaAccount::TILL
            $mpesaAcc = new MpesaAccount(
                config('services.sidooh.providers.mpesa.pay_bill.shortcode'),
                config('services.sidooh.providers.mpesa.pay_bill.key'),
                config('services.sidooh.providers.mpesa.pay_bill.secret'),
                config('services.sidooh.providers.mpesa.pay_bill.passkey')
            );

            $reference = $this->paymentDTO->reference;
        } else {
            $mpesaAcc = null;
        }

        return match ($this->paymentDTO->subtype) {
            PaymentSubtype::STK => mpesa_request($this->paymentDTO->source, $amount, $reference, null, $mpesaAcc)->id,
            default => throw new Exception('Unsupported payment subtype')
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

        return match ($this->paymentDTO->destinationSubtype) {
            PaymentSubtype::B2C => $this->b2CPayment(),
            PaymentSubtype::B2B => $this->b2BPayment(),
            default => throw new Exception('Unsupported payment subtype')
        };
    }

    private function b2CPayment(): int {
        $phone = $this->paymentDTO->destinationData['phone'];

        return mpesa_send($phone, $this->paymentDTO->amount, 'payment')->id;
    }

    private function b2BPayment(): int
    {
        $merchantType = MerchantType::tryFrom($this->paymentDTO->destinationData['merchant_type']);

        $amount = $this->paymentDTO->amount;
        $tillOrPaybill = $this->paymentDTO->destinationData['paybill_number'] ?? $this->paymentDTO->destinationData['buy_goods_number'] ?? $this->paymentDTO->destinationData['store'] ;
        $reference = $this->paymentDTO->destinationData['account_number'] ?? $this->paymentDTO->destinationData['buy_goods_number'] ?? '';
        $msisdn = SidoohAccounts::find($this->paymentDTO->accountId)['phone'];

        $merchantType = match ($merchantType) {
            MerchantType::MPESA_BUY_GOODS => B2BPayment::TILL,
            MerchantType::MPESA_PAY_BILL => B2BPayment::PAYBILL,
            MerchantType::MPESA_STORE => B2BPayment::STORE,
        };

        return mpesa_b2b($merchantType, $tillOrPaybill, $amount, $reference, $msisdn)->id;
    }
}
