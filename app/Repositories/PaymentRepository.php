<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\MpesaReference;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Models\Payment;
use App\Services\SidoohAccounts;
use Arr;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentRepository
{
    private float $totalAmount;

    private mixed $firstTransaction;

    /**
     * @param Collection $transactions
     * @param PaymentMethod $method
     * @param string $debit_account
     */
    public function __construct(
        private readonly Collection    $transactions,
        private readonly PaymentMethod $method,
        private readonly string        $debit_account)
    {
//            TODO: Change to actual amount on production
//        $this->totalAmount = $transactions->sum("amount");
        $this->totalAmount = 1;
        $this->firstTransaction = $this->transactions->first();
    }

    /**
     * @throws Throwable
     */
    public function process(): array
    {
        $pass = $this->transactions->every(fn($t) => $t['product_id'] === $this->firstTransaction['product_id']);
        if (!$pass) {
            throw new Exception("Transactions mismatch on product", 422);
        }

        return match ($this->method) {
            PaymentMethod::MPESA => $this->mpesa(),
            PaymentMethod::VOUCHER => $this->voucher(),
//            PaymentMethod::FLOAT->name => $this->float(),
            default => throw new Exception("Unsupported payment method!")
        };
    }

    /**
     * @throws Exception
     */
    public function mpesa(): array
    {
        // TODO: Ensure debit_acc is valid phone. May not be necessary since library validates
        $number = $this->debit_account;

        $productType = ProductType::from($this->firstTransaction['product_id']);
        $reference = match ($productType) {
            ProductType::AIRTIME => MpesaReference::AIRTIME,
            ProductType::VOUCHER => MpesaReference::PAY_VOUCHER,
            ProductType::UTILITY => MpesaReference::PAY_UTILITY,
            ProductType::SUBSCRIPTION => MpesaReference::SUBSCRIPTION,
            default => throw new Exception('Unexpected match value')
        };

        $stkResponse = mpesa_request($number, $this->totalAmount, $reference);

        $paymentData = $this->getPaymentData($stkResponse->id, PaymentType::MPESA, PaymentSubtype::STK);

        // TODO: Improve with: Payment insert with return
        $data['payments'] = $paymentData->map(
            fn($data) => Arr::only(
                Payment::create($data)->toArray(),
                ['id', 'amount', 'type', 'subtype', 'status', 'reference']
            )
        );

        return $data;
    }

    /**
     * @throws Exception|Throwable
     */
    public function voucher(): array
    {
        // TODO: Ensure debit_acc is valid id
        $id = $this->debit_account;
        SidoohAccounts::find($id);

        $productType = ProductType::from($this->firstTransaction['product_id']);
        $description = match ($productType) {
            ProductType::AIRTIME => Description::AIRTIME_PURCHASE,
            ProductType::VOUCHER => Description::VOUCHER_PURCHASE,
            ProductType::UTILITY => Description::UTILITY_PURCHASE,
            ProductType::SUBSCRIPTION => Description::SUBSCRIPTION_PURCHASE,
            default => throw new Exception('Unexpected match value')
        };

        if ($productType === ProductType::VOUCHER) {
            $pass = $this->transactions->every(fn($t) => isset($t['destination']) && SidoohAccounts::findByPhone($t['destination']));
            if (!$pass) {
                throw new Exception("Transactions need destination to be valid", 422);
            }
        }

        return DB::transaction(function () use ($id, $description, $productType) {

            [$voucher, $voucherTransaction] = VoucherRepository::debit($id, $this->totalAmount, $description);

            $data["debit_voucher"] = $voucher;

            if ($productType === ProductType::VOUCHER) {
                foreach ($this->transactions as $transaction) {
                    $account = SidoohAccounts::findByPhone($transaction['destination']);
                    [$voucher,] = VoucherRepository::credit($account['id'], $transaction["amount"], Description::VOUCHER_PURCHASE);
                    $data["credit_vouchers"][] = $voucher;
                }
            }

            $paymentData = $this->getPaymentData($voucherTransaction->id, PaymentType::SIDOOH, PaymentSubtype::VOUCHER, Status::COMPLETED);

            // TODO: Improve with: Payment insert with return
            $data['payments'] = $paymentData->map(
                fn($data) => Arr::only(
                    Payment::create($data)->toArray(),
                    ['id', 'amount', 'type', 'subtype', 'status', 'reference']
                )
            );


//        TODO: Test this
//        if ($productType === ProductType::UTILITY) $data["provider"] = $this->data["provider"];

            return $data;
        }, 3);
    }

    /**
     * @throws Throwable
     */
//    public function float(): Model|Payment
//    {
//        $float = FloatAccount::firstOrCreate([
//            'accountable_id' => $this->data['enterprise_id'],
//            'accountable_type' => "ENTERPRISE"
//        ]);
//
//        if ($float) {
//            $bal = $float->balance;
//
//            if ($bal < (int)$this->data['amount']) throw new Exception("Insufficient float balance!");
//        }
//
//        $float->balance -= $this->data['amount'];
//        $float->save();
//
//        $paymentData = $this->getPaymentData($float->id, $float->getMorphClass(), PaymentType::SIDOOH, PaymentSubtype::FLOAT);
//
//        $float->floatAccountTransaction()->create([
//            'amount' => $this->data['amount'],
//            'type' => TransactionType::DEBIT,
//            'description' => $this->transactions[0]["description"]
//        ]);
//
//        $this->data += $paymentData;
//
//        return Payment::create($this->data);
//    }

    public function getPaymentData(int $providableId, PaymentType $type, PaymentSubtype $subtype, Status $status = null): Collection
    {
        return $this->transactions->map(fn($transaction) => [
            "amount" => $transaction["amount"],
            "type" => $type->name,
            "subtype" => $subtype->name,
            "status" => $status->name ?? Status::PENDING->name,
            "provider_id" => $providableId,
            "reference" => $transaction["reference"] ?? null,
            "description" => $transaction["description"] . ' - ' . $transaction["destination"],
        ]);
    }


}
