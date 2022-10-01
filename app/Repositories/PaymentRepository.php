<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\MerchantType;
use App\Enums\MpesaReference;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Models\Payment;
use App\Services\SidoohAccounts;
use Arr;
use DrH\TendePay\Facades\TendePay;
use DrH\TendePay\Requests\BuyGoodsRequest;
use DrH\TendePay\Requests\PayBillRequest;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentRepository
{
    private float $totalAmount;

    private mixed $firstTransaction;

    private MerchantType $merchantType;

    private string $accountNumber = '';

    private string $tillOrPaybill;

    /**
     * @param  Collection  $transactions
     * @param  PaymentMethod  $method
     * @param  string  $debit_account
     */
    public function __construct(
        private readonly Collection $transactions,
        private readonly PaymentMethod $method,
        private readonly string $debit_account,
//        private readonly bool $isB2b
    ) {
        $this->totalAmount = $transactions->sum('amount');
        $this->firstTransaction = $this->transactions->first();
    }

    /**
     * @throws Throwable
     */
    public function process(): array
    {
        $pass = $this->transactions->every(fn($t) => $t['product_id'] === $this->firstTransaction['product_id']);
        if (! $pass) {
            throw new Exception('Transactions mismatch on product', 422);
        }

        return match ($this->method) {
            PaymentMethod::MPESA   => $this->mpesa(),
            PaymentMethod::VOUCHER => $this->voucher(),
//            PaymentMethod::FLOAT->name => $this->float(),
            default => throw new Exception('Unsupported payment method!')
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
            ProductType::AIRTIME      => MpesaReference::AIRTIME,
            ProductType::VOUCHER      => MpesaReference::PAY_VOUCHER,
            ProductType::UTILITY      => MpesaReference::PAY_UTILITY,
            ProductType::SUBSCRIPTION => MpesaReference::SUBSCRIPTION,
            default                   => throw new Exception('Unexpected match value')
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
            ProductType::AIRTIME      => Description::AIRTIME_PURCHASE,
            ProductType::VOUCHER      => Description::VOUCHER_PURCHASE,
            ProductType::UTILITY      => Description::UTILITY_PURCHASE,
            ProductType::SUBSCRIPTION => Description::SUBSCRIPTION_PURCHASE,
            ProductType::MERCHANT     => Description::MERCHANT_PAYMENT,
            default                   => throw new Exception('Unexpected match value')
        };

        if ($productType === ProductType::VOUCHER) {
            $pass = $this->transactions->every(fn($t) => isset($t['destination']) && SidoohAccounts::findByPhone($t['destination']));
            if (! $pass) {
                throw new Exception('Transactions need destination to be valid', 422);
            }
        }

        return DB::transaction(function() use ($id, $description, $productType) {
            [$voucher, $voucherTransaction] = VoucherRepository::debit($id, $this->totalAmount, $description);

            $data['debit_voucher'] = $voucher;

            if ($productType === ProductType::VOUCHER) {
                foreach ($this->transactions as $transaction) {
                    $account = SidoohAccounts::findByPhone($transaction['destination']);
                    [$voucher] = VoucherRepository::credit($account['id'], $transaction['amount'], Description::VOUCHER_PURCHASE);
                    $data['credit_vouchers'][] = $voucher;
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

            if ($productType === ProductType::MERCHANT) {
                $b2bRequest = match ($this->merchantType) {
                    MerchantType::MPESA_PAY_BILL  => new PayBillRequest($this->totalAmount, $this->accountNumber, $this->tillOrPaybill),
                    MerchantType::MPESA_BUY_GOODS => new BuyGoodsRequest($this->totalAmount, $this->accountNumber, $this->tillOrPaybill),
                    default                       => throw new Exception('Unexpected merchant type')
                };

                $tendePayRequest = TendePay::b2bRequest($b2bRequest);

                $paymentData = [
                    'amount'      => $this->totalAmount,
                    'type'        => PaymentType::SIDOOH->name,
                    'subtype'     => PaymentSubtype::B2B->name,
                    'status'      => Status::PENDING->name,
                    'provider_id' => $tendePayRequest->id,
                    'reference'   => $this->firstTransaction['reference'] ?? null,
                    'description' => $description->name.' - '.$this->tillOrPaybill,
                ];

                $data['b2b_payment'] = Payment::create($paymentData)->toArray();
            }

//        TODO: Test this
//        if ($productType === ProductType::UTILITY) $data["provider"] = $this->data["provider"];

            return $data;
        }, 3);
    }

    public function processB2b(MerchantType $merchantType, string $tillOrPaybill, string $accountNumber = ''): array
    {
        $pass = $this->transactions->every(fn($t) => $t['product_id'] === $this->firstTransaction['product_id']);
        if (! $pass) {
            throw new Exception('Transactions mismatch on product', 422);
        }

        $this->merchantType = $merchantType;
        $this->tillOrPaybill = $tillOrPaybill;
        $this->accountNumber = $accountNumber;

        return match ($this->method) {
            PaymentMethod::MPESA   => $this->mpesa(),
            PaymentMethod::VOUCHER => $this->voucher(),
//            PaymentMethod::FLOAT->name => $this->float(),
            default => throw new Exception('Unsupported payment method!')
        };
    }

    public function getPaymentData(int $providerId, PaymentType $type, PaymentSubtype $subtype, Status $status = null): Collection
    {
        return $this->transactions->map(fn($transaction) => [
            'amount'      => $transaction['amount'],
            'type'        => $type->name,
            'subtype'     => $subtype->name,
            'status'      => $status->name ?? Status::PENDING->name,
            'provider_id' => $providerId,
            'reference'   => $transaction['reference'] ?? null,
            'description' => $transaction['description'].' - '.$transaction['destination'],
        ]);
    }
}
