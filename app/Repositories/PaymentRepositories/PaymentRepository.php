<?php

namespace App\Repositories\PaymentRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Events\FloatTopUpEvent;
use App\Events\VoucherCreditEvent;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    /**
     * @throws Exception
     */
    public function __construct(private readonly PaymentDTO $paymentData, private readonly ?string $ipn = null)
    {
        Log::info('PaymentDTO', [$this->paymentData]);
    }

    public function getPaymentRepository(): Repository
    {
        $matchType = $this->paymentData->isWithdrawal ? $this->paymentData->destinationType : $this->paymentData->type;

        return match ($matchType) {
            PaymentType::MPESA  => new MpesaRepository($this->paymentData),
            PaymentType::SIDOOH => new SidoohRepository($this->paymentData),
            PaymentType::TENDE  => new TendeRepository($this->paymentData),
        };
    }

    /**
     * @throws Exception
     */
    public function processPayment(): Payment
    {
        $providerId = $this->getPaymentRepository()->process();

        if (! $this->paymentData->isWithdrawal) {
            $payment = $this->createPayment($providerId);

            // TODO: Should we fire event on voucher debit then consume?
            // Handle internal payment requests by immediately paying to intended
            if (in_array($this->paymentData->subtype, [PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT])) {
                if (in_array(
                    $this->paymentData->destinationSubtype,
                    [PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT, PaymentSubtype::B2B, PaymentSubtype::B2C]
                )) {
                    $repo = new PaymentRepository(
                        PaymentDTO::fromPayment($payment->refresh()), $payment->ipn
                    );

                    $repo->processPayment();
                }

                if (! $this->paymentData->destinationSubtype) {
                    $payment->update(['status' => Status::COMPLETED]);
                }
            }
        } else {
            $payment = $this->updatePayment($providerId);
        }

        return $payment;
    }

    private function updatePayment(int $providerId): Payment
    {
        $this->paymentData->payment->update(['destination_provider_id' => $providerId]);

        if ($this->paymentData->destinationSubtype === PaymentSubtype::VOUCHER) {
            event(new VoucherCreditEvent($this->paymentData->payment->destinationProvider));
        } elseif ($this->paymentData->destinationSubtype === PaymentSubtype::FLOAT) {
            event(new FloatTopUpEvent($this->paymentData->payment->destinationProvider));
        }

        return $this->paymentData->payment;
    }

    private function createPayment(int $providerId): Payment
    {
        $paymentData = [
            'amount'              => $this->paymentData->amount,
            'type'                => $this->paymentData->type,
            'subtype'             => $this->paymentData->subtype,
            'status'              => Status::PENDING,
            'provider_id'         => $providerId,
            'reference'           => $this->paymentData->reference,
            'description'         => $this->paymentData->description,
            'account_id'          => $this->paymentData->accountId,
            'ipn'                 => $this->ipn,
            'destination_type'    => $this->paymentData->destinationType,
            'destination_subtype' => $this->paymentData->destinationSubtype,
            'destination_data'    => $this->paymentData->destinationData,
            'charge'              => $this->paymentData->charge,
        ];

        return Payment::create($paymentData);
    }
}
