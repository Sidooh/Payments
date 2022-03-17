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
use App\Models\FloatAccount;
use App\Models\Payment;
use App\Models\Voucher;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Propaganistas\LaravelPhone\PhoneNumber;
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

        $paymentData = array_map(fn($transaction) => [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transaction["id"],
            'amount'        => $transaction["amount"],
            'type'          => PaymentType::MPESA->name,
            'subtype'       => PaymentSubtype::STK->name,
            'status'        => $this->data['product'] === 'subscription'
                ? Status::PENDING->name
                : Status::COMPLETED->name,
            'provider_id'   => $stkResponse->id,
            'provider_type' => $stkResponse->getMorphClass(),
            "created_at"    => now(),
            "updated_at"    => now(),
        ], $this->transactions);

        Payment::insert($paymentData);
    }

    /**
     * @throws Exception|Throwable
     */
    public function voucher()
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

        $paymentData = array_map(fn($transaction) => [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transaction["id"],
            'amount'        => $transaction["amount"],
            'type'          => PaymentType::SIDOOH->name,
            'subtype'       => PaymentSubtype::VOUCHER->name,
            'status'        => $this->data['product'] === 'subscription'
                ? Status::PENDING->name
                : Status::COMPLETED->name,
            'provider_id'   => $voucher->id,
            'provider_type' => $voucher->getMorphClass(),
            "created_at"    => now(),
            "updated_at"    => now(),
        ], $this->transactions);

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

    /**
     * @throws Throwable
     */
    public function float(): Model|Payment
    {
        $float = FloatAccount::firstOrCreate([
            'accountable_id'   => $this->data['enterprise_id'],
            'accountable_type' => "ENTERPRISE"
        ], []);

        if($float) {
            $bal = $float->balance;

            if($bal < (int)$this->data['amount']) throw new Exception("Insufficient float balance!");
        }

        $float->balance -= $this->data['amount'];
        $float->save();

        $paymentData = [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transactionId,
            'amount'        => $this->data['amount'],
            'type'          => PaymentType::SIDOOH,
            'subtype'       => PaymentSubtype::VOUCHER,
            'status'        => Status::COMPLETED,
            'provider_id'   => $float->id,
            'provider_type' => $float->getMorphClass(),
        ];

        if($this->data['product'] === 'subscription') {
            $paymentData['amount'] = $this->data['amount'];
            $paymentData['status'] = Status::PENDING;
        } else if($this->data['product'] === 'airtime') {
            $paymentData['phone'] = PhoneNumber::make($this->data['account']['phone'], 'KE')->formatE164();
        } else if($this->data['product'] === 'utility') {
            $paymentData['account_number'] = $this->data['account']['phone'];
        }

        if($this->data['product'] === 'merchant') $paymentData['status'] = Status::PENDING;

        $float->floatAccountTransaction()->create([
            'amount'      => $this->data['amount'],
            'type'        => TransactionType::DEBIT,
            'description' => $this->data['description']
        ]);

        $this->data += $paymentData;
        return Payment::create($this->data);
    }


    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param string $amount
     */
    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param bool $bulk
     */
    public function setBulk(bool $bulk): void
    {
        $this->bulk = $bulk;
    }
}
