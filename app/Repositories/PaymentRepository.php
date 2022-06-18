<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\MpesaReference;
use App\Enums\PayableType;
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
    public function mpesa()
    {
        $number = $this->data['debit_account'] ?? $this->data['payment_account']['phone'];

        $reference = match (ProductType::from($this->transactions[0]["product_id"])) {
            ProductType::AIRTIME => MpesaReference::AIRTIME,
            ProductType::VOUCHER => MpesaReference::PAY_VOUCHER,
            ProductType::UTILITY => MpesaReference::PAY_UTILITY,
            ProductType::SUBSCRIPTION => MpesaReference::AGENT_REGISTER,
            default => throw new \Exception('Unexpected match value')
        };

        try {
//            TODO: Change to actual amount on production
            $stkResponse = mpesa_request($number, 1, $reference, $this->transactions[0]["description"]);
        } catch (MpesaException $e) {
//            TODO: Inform customer of issue?
            Log::critical($e);
            return null;
        }

        $paymentData = $this->getPaymentData($stkResponse->id, $stkResponse->getMorphClass(), PaymentType::MPESA, PaymentSubtype::STK);

        Payment::insert($paymentData);
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

        $paymentData = $this->getPaymentData($voucher->id, $voucher->getMorphClass(), PaymentType::SIDOOH, PaymentSubtype::VOUCHER, Status::COMPLETED);

        $voucher->balance -= $this->amount;
        $voucher->save();
        $voucher->voucherTransaction()->create([
            'amount'      => $this->amount,
            'type'        => TransactionType::DEBIT->name,
            'description' => $this->transactions[0]["description"]
        ]);

        Payment::insert($paymentData);

        $data["payments"] = $paymentData;
        $data["vouchers"][] = $voucher;

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

    public function getPaymentData(int $providerId, string $providerType, PaymentType $type, PaymentSubtype $subtype, Status $status = null): array
    {
        return array_map(fn($transaction) => [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transaction["id"],
            'amount'        => $transaction["amount"],
            'type'          => $type->name,
            'subtype'       => $subtype->name,
            'status'        => $status->name ?? Status::PENDING->name,
            'provider_id'   => $providerId,
            'provider_type' => $providerType,
            "created_at"    => now(),
            "updated_at"    => now(),
        ], $this->transactions);
    }
}
