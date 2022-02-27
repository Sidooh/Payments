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
use Illuminate\Support\Facades\Log;
use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

class PaymentRepository
{
    private array $data;

    public function mpesa(int $transactionId, $amount): Model|Payment|null
    {
        $number = $this->data['mpesa_number'] ?? $this->data['phone'];

        $reference = match ($this->data['product']) {
            "airtime" => MpesaReference::AIRTIME,
            "voucher" => MpesaReference::PAY_VOUCHER,
            "utility" => MpesaReference::PAY_UTILITY,
            "subscription" => MpesaReference::AGENT_REGISTER
        };

        try {
            $stkResponse = mpesa_request($number, $this->data['amount'], $reference, $this->data['description']);
        } catch (MpesaException $e) {
//            TODO: Inform customer of issue?
            Log::critical($e);
            return null;
        }

        return Payment::create([
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transactionId,
            'amount'        => $amount,
            'status'        => Status::PENDING->name,
            'type'          => PaymentType::MPESA->name,
            'subtype'       => PaymentSubtype::STK->name,
            'provider_id'   => $stkResponse->id,
            'provider_type' => $stkResponse->getMorphClass(),
            'phone'         => PhoneNumber::make($this->data['destination'] ?? null, 'KE')->formatE164()
        ]);
    }

    /**
     * @throws Exception|Throwable
     */
    public function voucher($transactionId): Model|Payment
    {
        $account = $this->data['account'];
        $destination = $this->data['destination'];

        $voucher = Voucher::firstOrCreate(['account_id' => $account['id']], [
            ...$account,
            'type' => VoucherType::SIDOOH
        ]);

        if($voucher) {
            $bal = $voucher->balance;

            if($bal < (int)$this->data['amount']) throw new Exception("Insufficient voucher balance!");
        }

        $voucher->balance -= $this->data['amount'];
        $voucher->save();

        $paymentData = [
            'payable_type'  => PayableType::TRANSACTION->name,
            'payable_id'    => $transactionId,
            'amount'        => $this->data['amount'],
            'type'          => PaymentType::SIDOOH->name,
            'subtype'       => PaymentSubtype::VOUCHER->name,
            'status'        => Status::COMPLETED->name,
            'provider_id'   => $voucher->id,
            'provider_type' => $voucher->getMorphClass(),
        ];

        if($this->data['product'] === 'subscription') {
            $paymentData['status'] = Status::PENDING->name;
        } else if($this->data['product'] === 'airtime') {
            $paymentData['phone'] = PhoneNumber::make($destination, 'KE')->formatE164();
        } else if($this->data['product'] === 'utility') {
            $paymentData['account_number'] = $destination;
        }

        if($this->data['product'] === 'merchant') $paymentData['status'] = Status::PENDING->name;

        $voucher->voucherTransaction()->create([
            'amount'      => $this->data['amount'],
            'type'        => TransactionType::DEBIT->name,
            'description' => $this->data['description']
        ]);

        $this->data += $paymentData;

        $payment = Payment::create($this->data);

        PaymentSuccessEvent::dispatch($payment->payable_id, $this->data);

        return $payment;
    }

    /**
     * @throws Throwable
     */
    public function float(int $transactionId): Model|Payment
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
}
