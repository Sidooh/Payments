<?php

namespace App\Repositories\SidoohRepositories;

use App\Enums\Description;
use App\Enums\TransactionType;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherRepository
{
    public static function getDefaultVoucherForAccount(int $accountId): Voucher
    {
        return Voucher::firstOrCreate([
            'account_id'      => $accountId,
            'voucher_type_id' => 1,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public static function creditDefaultVoucherForAccount(int $accountId, float $amount, Description $description): VoucherTransaction
    {
        $voucher = VoucherRepository::getDefaultVoucherForAccount($accountId);

        return self::credit($voucher->id, $amount, $description);
    }

    /**
     * @throws Exception|Throwable
     */
    public static function credit(int $id, float $amount, Description $description): VoucherTransaction
    {
        $voucher = Voucher::findOrFail($id);

        // TODO: Check if voucher settings and voucher has limit, check this even before if possible
        if ($voucher->balance + $amount > $voucher->voucherType->limit_amount) {
            throw new Exception('Amount will exceed voucher limit.', 422);
        }

        return DB::transaction(function() use ($description, $amount, $voucher) {
            $voucher->balance += $amount;
            $voucher->save();

            return $voucher->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::CREDIT,
                'description' => $description,
            ]);
        }, 2);
    }

    /**
     * @throws Exception|Throwable
     */
    public static function debit(int $id, float $amount, Description $description): VoucherTransaction
    {
        $voucher = Voucher::findOrFail($id);

        // TODO: Return proper response/ create specific error type, rather than throwing error
        if ($voucher->balance < $amount) {
            throw new Exception('Insufficient voucher balance.', 422);
        }

        return DB::transaction(function() use ($description, $amount, $voucher) {
            $voucher->balance -= $amount;
            $voucher->save();

            return $voucher->transactions()->create([
                'amount'      => $amount,
                'type'        => TransactionType::DEBIT,
                'description' => $description,
            ]);
        }, 2);
    }
}
