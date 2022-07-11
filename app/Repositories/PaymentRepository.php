<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\MpesaReference;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Enums\TransactionType;
use App\Enums\VoucherType;
use App\Models\FloatAccount;
use App\Models\Payment;
use App\Models\Voucher;
use App\Services\SidoohAccounts;
use Arr;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * @throws \Exception
     */
    public function mpesa(): ?array
    {
        $number = $this->data['debit_account'] ?? $this->data['payment_account']['phone'];

        $productType = ProductType::from($this->transactions[0]["product_id"]);
        $reference = match ($productType) {
            ProductType::AIRTIME => MpesaReference::AIRTIME,
            ProductType::VOUCHER => MpesaReference::PAY_VOUCHER,
            ProductType::UTILITY => MpesaReference::PAY_UTILITY,
            ProductType::SUBSCRIPTION => MpesaReference::SUBSCRIPTION,
            default => throw new \Exception('Unexpected match value')
        };

        try {
//            TODO: Change to actual amount on production
            $stkResponse = mpesa_request($number, 1, $reference, $this->transactions[0]["description"]);

            $paymentData = $this->getPaymentData($stkResponse->id, $stkResponse->getMorphClass(), PaymentType::MPESA, PaymentSubtype::STK);
            if($productType == ProductType::VOUCHER) $paymentData[0]['details'] = $this->transactions[0]['destination'];

            $data["payments"] = [];
            foreach($paymentData as $payment) $data["payments"][] = [
                ...Arr::only($payment, ["transaction_id", "status"]),
                "payment_id"     => Payment::create($payment)->id,
            ];

            return $data;
        } catch (MpesaException $e) {
//            TODO: Inform customer of issue?
            Log::critical($e);
            return null;
        }
    }

    /**
     * @throws Exception|Throwable
     */
    public function voucher(): array
    {
        $account = $this->data['payment_account'];

        $voucher = Voucher::firstOrCreate(['account_id' => $account['id']], [
            ...$account,
            'type' => VoucherType::SIDOOH
        ]);

        // TODO: Return proper response, rather than throwing error
        if($voucher->balance < (int)$this->amount) throw new Exception("Insufficient voucher balance!");

        $voucher->balance -= $this->amount;
        $voucher->save();
        $voucherTransaction = $voucher->voucherTransactions()->create([
            'amount'      => $this->amount,
            'type'        => TransactionType::DEBIT->name,
            'description' => $this->transactions[0]["description"]
        ]);

        $paymentData = $this->getPaymentData($voucherTransaction->id, $voucherTransaction->getMorphClass(), PaymentType::SIDOOH, PaymentSubtype::VOUCHER, Status::COMPLETED);

        $data["payments"] = [];
        foreach($paymentData as $payment) $data["payments"][] = [
            ...Arr::only($payment, ["transaction_id", "status"]),
            "payment_id"     => Payment::create($payment)->id,
        ];

        $data["vouchers"][] = $voucher->only(["type", "balance", "account_id"]);

        $productType = ProductType::from($this->transactions[0]["product_id"]);
        if($productType === ProductType::UTILITY) $data["provider"] = $this->data["provider"];

        if($productType === ProductType::VOUCHER) {
            foreach($this->transactions as $trans) {
                ["id" => $accountId] = SidoohAccounts::findByPhone($trans['destination']);

                $data["vouchers"][] = VoucherRepository::credit($accountId, $trans["amount"], Description::VOUCHER_PURCHASE, true);
            }
        }

        return $data;
    }

    /**
     * @throws Throwable
     */
    public function float(): Model|Payment
    {
        $float = FloatAccount::firstOrCreate([
            'accountable_id'   => $this->data['enterprise_id'],
            'accountable_type' => "ENTERPRISE"
        ]);

        if($float) {
            $bal = $float->balance;

            if($bal < (int)$this->data['amount']) throw new Exception("Insufficient float balance!");
        }

        $float->balance -= $this->data['amount'];
        $float->save();

        $paymentData = $this->getPaymentData($float->id, $float->getMorphClass(), PaymentType::SIDOOH, PaymentSubtype::FLOAT);

        $float->floatAccountTransaction()->create([
            'amount'      => $this->data['amount'],
            'type'        => TransactionType::DEBIT,
            'description' => $this->transactions[0]["description"]
        ]);

        $this->data += $paymentData;

        return Payment::create($this->data);
    }

    public function getPaymentData(int $providableId, string $providableType, PaymentType $type, PaymentSubtype $subtype, Status $status = null): array
    {
        return array_map(fn($transaction) => [
            "transaction_id"  => $transaction["id"],
            "amount"          => $transaction["amount"],
            "details"         => $transaction["destination"],
            "type"            => $type->name,
            "subtype"         => $subtype->name,
            "status"          => $status->name ?? Status::PENDING->name,
            "providable_id"   => $providableId,
            "providable_type" => $providableType,
        ], $this->transactions);
    }
}
