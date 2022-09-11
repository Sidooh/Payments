<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\Initiator;
use App\Enums\TransactionType;
use App\Models\FloatAccount;
use Exception;

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
    public function topUp(Initiator $initiator, $amount, ?int $accountId, ?int $enterpriseId): array
    {
        $floatAccount = FloatAccount::whereFloatableType($initiator)->whereFloatableId(match ($initiator) {
            Initiator::ENTERPRISE => $enterpriseId,
            Initiator::AGENT => $accountId,
            default => throw new Exception('Unexpected initiator value.'),
        })->first();

        return FloatAccountRepository::credit($floatAccount, $amount, Description::FLOAT_PURCHASE);
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
