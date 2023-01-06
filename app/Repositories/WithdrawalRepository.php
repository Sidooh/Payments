<?php

namespace App\Repositories;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Models\Payment;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class WithdrawalRepository
{
    /**
     * @param  array  $transaction
     */
    public function __construct(private readonly array $transaction)
    {
    }

    /**
     * @throws \Exception|Throwable
     */
    public function mpesa()
    {
        $number = $this->transaction['destination'];

        // TODO: Does this need to be a loop to cater for multiple transactions?
        $productType = ProductType::WITHDRAWAL->name;
        $reference = $productType.' - '.$number;

        try {
            //TODO: Check amount here is less than available, make a b2c query call and/or check the b2c table
            $b2cResponse = mpesa_send($number, $this->transaction['amount'], $reference);
        } catch (MpesaException $e) {
//            TODO: Inform customer of issue?
            Log::critical($e);

            return null;
        }

        $paymentData = $this->getPaymentData($b2cResponse->id, $b2cResponse->getMorphClass(), PaymentType::MPESA, PaymentSubtype::B2C);

        return Payment::create($paymentData);
    }

    /**
     * @throws Exception|Throwable
     */
//    public function voucher(): array
//    {
//        $account = $this->data['payment_account'];
//
//        $voucher = Voucher::firstOrCreate(['account_id' => $account['id']], [
//            ...$account,
//            'type' => VoucherType::SIDOOH
//        ]);
//
//        // TODO: Return proper response, rather than throwing error
//        if ($voucher->balance < (int)$this->amount) throw new Exception("Insufficient voucher balance!");
//
//        $paymentData = $this->getPaymentData($voucher->id, $voucher->getMorphClass(), PaymentType::SIDOOH, PaymentSubtype::VOUCHER, Status::COMPLETED);
//
//        $voucher->balance -= $this->amount;
//        $voucher->save();
//        $voucher->voucherTransaction()->create([
//            'amount' => $this->amount,
//            'type' => TransactionType::DEBIT->name,
//            'description' => $this->transactions[0]["description"]
//        ]);
//
//        Payment::insert($paymentData);
//
//        $data["payments"] = $paymentData;
//        $data["vouchers"][] = $voucher;
//
//        $productType = ProductType::from($this->transactions[0]["product_id"]);
//        if ($productType === ProductType::UTILITY) $data["provider"] = $this->data["provider"];
//
//        if ($productType === ProductType::VOUCHER) {
//            foreach ($this->transactions as $trans) {
//                ["id" => $accountId] = SidoohAccounts::findByPhone($trans['destination']);
//
//                $data["vouchers"][] = VoucherRepository::credit($accountId, $trans["amount"], Description::VOUCHER_PURCHASE, true);
//            }
//        }
//
//        return $data;
//    }

    public function getPaymentData(int $providerId, string $providerType, PaymentType $type, PaymentSubtype $subtype, Status $status = null): array
    {
        return [
            'payable_type'  => $this->transaction['payable_type'],
            'payable_id'    => $this->transaction['payable_id'],
            'amount'        => $this->transaction['amount'],
            'details'       => $this->transaction['destination'],
            'type'          => $type->name,
            'subtype'       => $subtype->name,
            'status'        => $status->name ?? Status::PENDING->name,
            'provider_id'   => $providerId,
            'provider_type' => $providerType,
        ];
    }
}
