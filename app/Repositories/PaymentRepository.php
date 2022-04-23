<?php

namespace App\Repositories;

use App\Enums\MpesaReference;
use App\Enums\PayableType;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Enums\TransactionType;
use App\Enums\VoucherType;
use App\Events\PaymentSuccessEvent;
use App\Models\Payment;
use App\Models\Voucher;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentRepository
{
    private array $data, $transactions;
    private string $amount;

    /**
     * @param array  $transactions
     * @param string $amount
     * @param array  $data
     */
    public function __construct(array $transactions, string $amount, array $data)
    {
        $this->transactions = $transactions;
        $this->amount = $amount;
        $this->data = $data;
    }

    public function mpesa()
    {
        $number = $this->data['mpesa_number'] ?? $this->data['payment_account']['phone'];

        $reference = match ($this->data['product']) {
            "airtime" => MpesaReference::AIRTIME,
            "voucher" => MpesaReference::PAY_VOUCHER,
            "utility" => MpesaReference::PAY_UTILITY,
            "subscription" => MpesaReference::AGENT_REGISTER
        };

        try {
            $stkResponse = mpesa_request($number, $this->amount, $reference, $this->transactions[0]["description"]);
        } catch (MpesaException $e) {
//            TODO: Inform customer of issue?
            Log::critical($e);
            return null;
        }

        $paymentData = $this->getPaymentData(
            $stkResponse->id,
            $stkResponse->getMorphClass(),
            PaymentType::MPESA,
            PaymentSubtype::STK
        );

        Payment::insert($paymentData);
    }

    /**
     * @throws Exception|Throwable
     */
    public function voucher(): void
    {
        $account = $this->data['payment_account'];

        $voucher = Voucher::firstOrCreate(['account_id' => $account['id']], [
            ...$account,
            'type' => VoucherType::SIDOOH
        ]);

        if($voucher) {
            $bal = $voucher->balance;

            if($bal < (int)$this->amount) throw new Exception("Insufficient voucher balance!");
        }

        $paymentData = $this->getPaymentData(
            $voucher->id,
            $voucher->getMorphClass(),
            PaymentType::SIDOOH,
            PaymentSubtype::VOUCHER
        );

        $voucher->balance -= $this->amount;
        $voucher->save();
        $voucher->voucherTransaction()->create([
            'amount'      => $this->amount,
            'type'        => TransactionType::DEBIT->name,
            'description' => $this->transactions[0]["description"]
        ]);

        Payment::insert($paymentData);

        $data = [
            "payments" => $paymentData,
            "product"  => $this->data["product"]
        ];

        if($data["product"] === "utility") $data["provider"] = $this->data["provider"];

        PaymentSuccessEvent::dispatch(Arr::pluck($this->transactions, 'id'), $data);
    }

    public function getPaymentData(int $providerId, string $providerType, PaymentType $type, PaymentSubtype $subtype): array
    {
        return array_map(fn($transaction) => [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transaction["id"],
            'amount'        => $transaction["amount"],
            'type'          => $type->name,
            'subtype'       => $subtype->name,
            'status'        => $this->data['product'] === 'subscription'
                ? Status::PENDING->name
                : Status::COMPLETED->name,
            'provider_id'   => $providerId,
            'provider_type' => $providerType,
            "created_at"    => now(),
            "updated_at"    => now(),
        ], $this->transactions);
    }
}
