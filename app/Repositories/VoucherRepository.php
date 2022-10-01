<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\TransactionType;
use App\Enums\VoucherType;
use App\Helpers\ApiResponse;
use App\Models\FloatAccount;
use App\Models\FloatAccountTransaction;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherRepository
{
    use ApiResponse;

    public static function credit(int $accountId, float $amount, Description $description): array
    {
        $voucher = Voucher::firstOrCreate([
            'account_id' => $accountId,
            'type'       => VoucherType::SIDOOH,
        ]);

        $voucher->balance += $amount;
        $voucher->save();

        $transaction = $voucher->voucherTransactions()->create([
            'amount'      => $amount,
            'type'        => TransactionType::CREDIT,
            'description' => $description,
        ]);

        return [$voucher->only(['type', 'balance', 'account_id']), $transaction];
    }

    /**
     * @throws Exception
     */
    public static function debit(int $accountId, float $amount, Description $description): array
    {
        $voucher = Voucher::firstOrCreate([
            'account_id' => $accountId,
            'type'       => VoucherType::SIDOOH,
        ]);

        // TODO: Return proper response/ create specific error type, rather than throwing error
        if ($voucher->balance < $amount) {
            throw new Exception('Insufficient voucher balance.', 422);
        }

        $voucher->balance -= $amount;
        $voucher->save();

        $transaction = $voucher->voucherTransactions()->create([
            'amount'      => $amount,
            'type'        => TransactionType::CREDIT,
            'description' => $description,
        ]);

        return [$voucher->only(['type', 'balance', 'account_id']), $transaction];
    }

    /**
     * @throws Exception|Throwable
     */
    public static function disburse(array $enterprise, array $accounts, $amount, VoucherType $voucherType)
    {
        return DB::transaction(function() use ($amount, $accounts, $enterprise, $voucherType) {
            $floatAccount = FloatAccount::firstWhere([
                'floatable_type' => 'ENTERPRISE',
                'floatable_id'   => $enterprise['id'],
            ]);

            $vouchers = Voucher::whereEnterpriseId($enterprise['id'])->whereIn('account_id', $accounts)
                ->whereType($voucherType)->get();

            if ($vouchers->isEmpty()) {
                Voucher::insert(array_map(fn($accId) => [
                    'account_id'    => $accId,
                    'enterprise_id' => $enterprise['id'],
                    'type'          => $voucherType,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ], $accounts));

                VoucherRepository::disburse($enterprise, $accounts, $amount, $voucherType);
            }

            $voucherTopUpAmount = function(Voucher $voucher) use ($enterprise) {
                $disburseType = match (VoucherType::from($voucher->type)) {
                    VoucherType::ENTERPRISE_LUNCH   => 'lunch',
                    VoucherType::ENTERPRISE_GENERAL => 'general',
                    default                         => throw new Exception('Unexpected match value'),
                };

                ['max' => $max] = collect($enterprise['settings'])->firstWhere('type', $disburseType);

                return $max - $voucher->balance;
            };

            $floatDebitAmount = $vouchers->reduce(fn($val, Voucher $voucher) => $val + $voucherTopUpAmount($voucher));

            if ($floatDebitAmount < 1) {
                return null;
            }
            if ($floatAccount->balance < $floatDebitAmount) {
                throw new Exception('Insufficient float balance!', 422);
            }

            $creditVouchers = $vouchers->map(function(Voucher $voucher) use ($voucherTopUpAmount) {
                return [
                    'type'          => $voucher->type,
                    'enterprise_id' => $voucher->enterprise_id,
                    'account_id'    => $voucher->account_id,
                    'balance'       => (float) $voucher->balance + (float) $voucherTopUpAmount($voucher),
                ];
            })->toArray();

            $voucherTransactions = $vouchers->map(function(Voucher $voucher) use ($voucherTopUpAmount) {
                return [
                    'voucher_id'  => $voucher->id,
                    'type'        => TransactionType::CREDIT,
                    'amount'      => $voucherTopUpAmount($voucher),
                    'description' => Description::VOUCHER_DISBURSEMENT->name,
                    'created_at'  => now(),
                ];
            })->toArray();

            $floatTransactions = $vouchers->map(function(Voucher $voucher) use ($voucherTopUpAmount, $floatAccount) {
                return [
                    'float_account_id' => $floatAccount->id,
                    'type'             => TransactionType::DEBIT,
                    'amount'           => $voucherTopUpAmount($voucher),
                    'description'      => Description::VOUCHER_DISBURSEMENT->name,
                    'created_at'       => now(),
                ];
            })->toArray();

            FloatAccountTransaction::insert($floatTransactions);
            Voucher::upsert($creditVouchers, ['account_id', 'enterprise_id', 'type'], ['balance']);
            VoucherTransaction::insert($voucherTransactions);

            $floatAccount->balance -= $floatDebitAmount;
            $floatAccount->save();

            return $floatAccount;
        });
    }
}
