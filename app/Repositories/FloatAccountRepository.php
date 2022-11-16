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
    public function store(Initiator $initiator, int $floatableId, int $accountId): FloatAccount
    {
        return FloatAccount::create([
            'floatable_id'   => $floatableId,
            'floatable_type' => $initiator,
            'account_id' => $accountId,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function topUp(FloatAccount $floatAccount, Initiator $initiator, $amount): Payment|Model
    {
        $phone = match ($floatAccount->floatable_type) {
            Initiator::AGENT->value      => SidoohAccounts::find($floatAccount->floatable_id)['phone'],
            Initiator::ENTERPRISE->value => SidoohProducts::findEnterprise($floatAccount->floatable_id)['admin']['account']['phone'],
            default                      => throw new Exception('Unexpected initiator value.')
        };

        $stkResponse = mpesa_request($phone, 1, MpesaReference::FLOAT);

        return Payment::create([
            'amount'      => $amount,
            'type'        => PaymentType::MPESA,
            'subtype'     => PaymentSubtype::STK,
            'status'      => Status::PENDING->name,
            'provider_id' => $stkResponse->id,
            'description' => Description::FLOAT_PURCHASE->value.' - '.$floatAccount->floatable_id,
        ]);
    }

    public static function credit(FloatAccount $floatAccount, float $amount, Description $description): array
    {
        $floatAccount->balance += $amount;
        $floatAccount->save();

        return [
            'float_account' => $floatAccount->only(['floatable_id', 'balance', 'floatable_type']),
            'transaction'   => $floatAccount->floatAccountTransactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::CREDIT,
                'description' => $description,
            ]),
        ];
    }
}
