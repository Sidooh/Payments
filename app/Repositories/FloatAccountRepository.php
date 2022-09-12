<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\Initiator;
use App\Enums\MpesaReference;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Enums\TransactionType;
use App\Models\FloatAccount;
use App\Models\Payment;
use App\Services\SidoohAccounts;
use App\Services\SidoohProducts;
use Exception;
use Illuminate\Database\Eloquent\Model;

class FloatAccountRepository
{
    /**
     * @throws \Exception
     */
    public function store(Initiator $initiator, ?int $accountId, ?int $enterpriseId): FloatAccount
    {
        return FloatAccount::create([
            'floatable_id'   => match ($initiator) {
                Initiator::ENTERPRISE => $enterpriseId,
                Initiator::AGENT => $accountId,
                default => throw new Exception('Unexpected initiator value.'),
            },
            'floatable_type' => $initiator
        ]);
    }

    /**
     * @throws \Exception
     */
    public function topUp(Initiator $initiator, $amount, ?int $accountId, ?int $enterpriseId): Payment|Model
    {
        $payParams = match ($initiator) {
            Initiator::AGENT => [
                "phone"        => SidoohAccounts::find($accountId)["phone"],
                "floatable_id" => $accountId
            ],
            Initiator::ENTERPRISE => [
                "phone"        => SidoohProducts::findEnterprise($enterpriseId)["admin"]["account"]["phone"],
                "floatable_id" => $enterpriseId
            ],
            default => throw new Exception('Unexpected initiator value.')
        };

        $floatAccount = FloatAccount::whereFloatableType($initiator)->whereFloatableId($payParams["floatable_id"])
            ->firstOrFail();

        return $this->pay($floatAccount->id, $payParams["phone"], $amount);
    }

    public function pay(int $floatAccountId, $number, $amount): Model|Payment
    {
        $stkResponse = mpesa_request($number, 1, MpesaReference::FLOAT);

        return Payment::create([
            "amount"      => $amount,
            "type"        => PaymentType::MPESA,
            "subtype"     => PaymentSubtype::STK,
            "status"      => Status::PENDING->name,
            "provider_id" => $stkResponse->id,
            "description" => Description::FLOAT_PURCHASE->value . ' - ' . $floatAccountId,
        ]);
    }

    public static function credit(FloatAccount $floatAccount, float $amount, Description $description): array
    {
        $floatAccount->balance += $amount;
        $floatAccount->save();

        return [
            "float_account" => $floatAccount->only(["floatable_id", "balance", "floatable_type"]),
            "transaction"   => $floatAccount->floatAccountTransaction()->create([
                'amount'      => $amount,
                'type'        => TransactionType::CREDIT,
                'description' => $description
            ])
        ];
    }
}
