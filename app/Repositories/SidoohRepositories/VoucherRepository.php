<?php

namespace App\Repositories\SidoohRepositories;

use App\Enums\TransactionType;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherRepository
{
    /**
     * @throws Exception|Throwable
     */
    public static function credit(int $id, float $amount, string $description): VoucherTransaction
    {
        $voucher = Voucher::findOrFail($id);

        // TODO: Check if voucher settings and voucher has limit, check this even before if possible
        if ($voucher->balance + $amount > $voucher->limit) {
            throw new Exception('Amount will exceed voucher limit.', 422);
        }

        return DB::transaction(function () use ($description, $amount, $voucher) {
            $voucher->balance += $amount;
            $voucher->save();

            $transaction = $voucher->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::CREDIT,
                'description' => $description,
            ]);

            return $transaction;
        }, 2);
    }

    /**
     * @throws Exception
     */
    public static function debit(int $id, float $amount, string $description): VoucherTransaction
    {
        $voucher = Voucher::findOrFail($id);

        // TODO: Return proper response/ create specific error type, rather than throwing error
        if ($voucher->balance < $amount) {
            throw new Exception('Insufficient voucher balance.', 422);
        }

        return DB::transaction(function () use ($description, $amount, $voucher) {
            $voucher->balance -= $amount;
            $voucher->save();

            $transaction = $voucher->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::DEBIT,
                'description' => $description,
            ]);

            return $transaction;
        }, 2);
    }
}
