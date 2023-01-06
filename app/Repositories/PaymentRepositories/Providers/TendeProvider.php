<?php

namespace App\Repositories\PaymentRepositories\Providers;

use App\DTOs\PaymentDTO;
use App\Enums\MerchantType;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Repositories\SidoohRepositories\VoucherRepository;
use DrH\TendePay\Exceptions\TendePayException;
use DrH\TendePay\Facades\TendePay;
use DrH\TendePay\Requests\BuyGoodsRequest;
use DrH\TendePay\Requests\PayBillRequest;
use Exception;
use Throwable;

class TendeProvider implements PaymentContract
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
            PaymentSubtype::VOUCHER => VoucherRepository::debit($this->paymentDTO->source, $this->paymentDTO->amount, $this->paymentDTO->description)->id,
            PaymentSubtype::FLOAT   => FloatAccountRepository::debit($this->paymentDTO->source, $this->paymentDTO->amount, $this->paymentDTO->description)->id,
            default                 => throw new Exception('Unsupported payment subtype')
        };
    }

    /**
     * @throws Throwable
     */
    private function requestDestinationPayment(): int
    {
        if ($this->paymentDTO->destinationType !== PaymentType::TENDE) {
            throw new Exception('Unsupported payment type');
        }

        return match ($this->paymentDTO->destinationSubtype) {
            PaymentSubtype::B2B => $this->tendePay(),
            default             => throw new Exception('Unsupported payment subtype')
        };
    }

    /**
     * @throws TendePayException
     * @throws Exception
     */
    private function tendePay(): int
    {
        $amount = $this->paymentDTO->amount;
        $tillOrPaybill = $this->paymentDTO->destinationData['paybill_number'] ?? $this->paymentDTO->destinationData['till_number'];
        $accountNumber = $this->paymentDTO->destinationData['account_number'] ?? $this->paymentDTO->destinationData['till_number'];

        $merchantType = MerchantType::tryFrom($this->paymentDTO->destinationData['merchant_type']);
        $b2bRequest = match ($merchantType) {
            MerchantType::MPESA_PAY_BILL  => new PayBillRequest($amount, $accountNumber, $tillOrPaybill),
            MerchantType::MPESA_BUY_GOODS => new BuyGoodsRequest($amount, $accountNumber, $tillOrPaybill),
            default                       => throw new Exception('Unsupported merchant type')
        };

        $tendePayRequest = TendePay::b2bRequest($b2bRequest);

        return $tendePayRequest->id;
    }
}
