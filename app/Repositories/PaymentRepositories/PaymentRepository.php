<?php

namespace App\Repositories\PaymentRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Events\FloatTopupEvent;
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
        Log::info("PaymentDTO", [$this->paymentData]);
    }

    function getPaymentRepository(): Repository
    {
        $matchType = $this->paymentData->isWithdrawal ? $this->paymentData->destinationType : $this->paymentData->type;
        return match ($matchType) {
            PaymentType::MPESA => new MpesaRepository($this->paymentData),
            PaymentType::SIDOOH => new SidoohRepository($this->paymentData),
        };
    }

    function processPayment(): Payment
    {
        $providerId = $this->getPaymentRepository()->process();

        if (!$this->paymentData->isWithdrawal) {
            $payment = $this->createPayment($providerId);

            // TODO: Should we fire event on voucher debit then consume?
            if (in_array($this->paymentData->destinationSubtype, [PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT])) {
                $repo = new PaymentRepository(
                    PaymentDTO::fromPayment($payment->refresh()),
                    $payment->ipn
                );

                $repo->processPayment();
            }

        } else {
            // TODO: Update payment
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
            event(new FloatTopupEvent($this->paymentData->payment->destinationProvider));
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
        ];

        return Payment::create($paymentData);
    }
}
