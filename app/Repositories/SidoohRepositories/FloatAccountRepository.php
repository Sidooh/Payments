<?php

namespace App\Repositories\SidoohRepositories;

use App\Enums\Description;
use App\Enums\TransactionType;
use App\Models\FloatAccount;
use App\Models\FloatAccountTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class FloatAccountRepository
{
    /**
     * @throws Exception|Throwable
     */
    public static function credit(int $id, int $amount, Description $description): FloatAccountTransaction
    {
        $account = FloatAccount::findOrFail($id);

        return DB::transaction(function() use ($description, $amount, $account) {
            $account->balance += $amount;
            $account->save();

            return $account->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::CREDIT,
                'description' => $description,
            ]);
        }, 2);
    }

    /**
     * @throws Exception|Throwable
     */
    public static function debit(int $id, float $amount, Description $description): FloatAccountTransaction
    {
        $account = FloatAccount::findOrFail($id);

        // TODO: Return proper response/ create specific error type, rather than throwing error
        if ($account->balance < $amount) {
            throw new Exception('Insufficient float balance.', 422);
        }

        return DB::transaction(function() use ($description, $amount, $account) {
            $account->balance -= $amount;
            $account->save();

            return $account->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::DEBIT,
                'description' => $description,
            ]);
        }, 2);
    }
}
