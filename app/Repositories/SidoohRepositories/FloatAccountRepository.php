<?php

namespace App\Repositories\SidoohRepositories;

use App\Enums\TransactionType;
use App\Exceptions\BalanceException;
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
    public static function credit(int $id, int $amount, string $description, int $charge = 0, array $extra = null): FloatAccountTransaction
    {
        $account = FloatAccount::findOrFail($id);

        return DB::transaction(function() use ($extra, $charge, $description, $amount, $account) {
            $account->balance += $amount + $charge;
            $account->save();

            if ($charge > 0) {
                $gl = FloatAccount::findOrFail(1);
                $gl->transactions()->create([
                    'amount'      => $charge,
                    'balance'     => $gl->balance - $charge,
                    'type'        => TransactionType::DEBIT,
                    'description' => "$description CHARGE REFUND",
                ]);
                $gl->decrement('balance', $charge);
            }

            return $account->transactions()->create([
                'amount'      => $amount + $charge,
                'balance'      => $account->balance,
                'type'        => TransactionType::CREDIT,
                'description' => $description,
                'extra' => $extra,
            ]);
        }, 2);
    }

    /**
     * @throws Exception|Throwable
     */
    public static function debit(int $id, float $amount, string $description, int $charge = 0): FloatAccountTransaction
    {
        $account = FloatAccount::findOrFail($id);

        // TODO: Return proper response/ create specific error type, rather than throwing error
        if ($account->balance <  ($amount + $charge)) {
            throw new BalanceException('Insufficient float balance.');
        }

        return DB::transaction(function() use ($amount, $charge, $description, $account) {
            $currentBalance = $account->balance;
            $account->balance -= ($amount + $charge);
            $account->save();

            $transaction = [
                'amount'      => $amount,
                'balance'   => $account->balance,
                'type'        => TransactionType::DEBIT,
                'description' => $description,
            ];

            if ($charge > 0) {
                $chargeTransaction = $account->transactions()->create([
                    'amount'      => $charge,
                    'balance'     => $currentBalance - $charge,
                    'type'        => TransactionType::CHARGE,
                    'description' => $description.' Charge',
                ]);

                $gl = FloatAccount::findOrFail(1);
                $gl->transactions()->create([
                    'amount'      => $charge,
                    'balance'     => $gl->balance + $charge,
                    'type'        => TransactionType::CREDIT,
                    'description' => $description.' Charge',
                    'extra'       => ['charge_transaction_id' => $chargeTransaction->id],
                ]);
                $gl->increment('balance', $charge);

                $transaction['extra'] = ['charge_transaction_id' => $chargeTransaction->id];
            }

            return $account->transactions()->create($transaction);
        }, 2);
    }
}
