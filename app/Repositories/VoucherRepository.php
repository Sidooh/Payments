<?php

namespace App\Repositories;

use App\Enums\Description;
use App\Enums\TransactionType;
use App\Events\VoucherPurchaseEvent;
use App\Helpers\ApiResponse;
use App\Models\FloatAccount;
use App\Models\FloatAccountTransaction;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherRepository
{
    use ApiResponse;

    public static function credit(int $accountId, $amount, $notify): Model|Builder|Voucher
    {
        $voucher = Voucher::whereAccountId($accountId)->firstOrFail();

        $voucher->balance += (double)$amount;
        $voucher->save();

        if($notify) VoucherPurchaseEvent::dispatch($voucher, $amount);

        return $voucher;
    }

    /**
     * @throws Exception|Throwable
     */
    public static function disburse(int $enterpriseId, array $disburseData)
    {
        return DB::transaction(function() use ($enterpriseId, $disburseData) {
            $floatAccount = FloatAccount::firstWhere([
                'accountable_type' => "ENTERPRISE",
                "accountable_id"   => $enterpriseId
            ]);

            $vouchers = Voucher::whereEnterpriseId($disburseData['enterprise_id'])
                ->whereIn('account_id', $disburseData['accounts'])
                ->whereType("ENTERPRISE_{$disburseData['disburse_type']}")->get();

            if($vouchers->isEmpty()) return;

            $floatDebitAmount = $vouchers->sum('voucher_top_up_amount');

            if($floatDebitAmount < 1) return;
            if($floatAccount->balance < $floatDebitAmount) throw new Exception('Insufficient float balance!', 422);

            $creditVouchers = $vouchers->map(function(Voucher $voucher) {
                return [
                    'type'          => $voucher->type,
                    'enterprise_id' => $voucher->enterprise_id,
                    'account_id'    => $voucher->account_id,
                    'balance'       => (double)$voucher->balance + (double)$voucher->voucher_top_up_amount,
                ];
            })->toArray();

            $voucherTransactions = $vouchers->map(function(Voucher $voucher) {
                return [
                    'voucher_id'  => $voucher->id,
                    'type'        => TransactionType::CREDIT,
                    'amount'      => $voucher->voucher_top_up_amount,
                    'description' => Description::VOUCHER_DISBURSEMENT
                ];
            })->toArray();

            $floatTransactions = $vouchers->map(function(Voucher $voucher) use ($floatAccount) {
                return [
                    'float_account_id' => $floatAccount->id,
                    'type'             => TransactionType::DEBIT,
                    'amount'           => $voucher->voucher_top_up_amount,
                    'description'      => Description::VOUCHER_DISBURSEMENT
                ];
            })->toArray();

            FloatAccountTransaction::insert($floatTransactions);
            Voucher::upsert($creditVouchers, ['account_id', 'enterprise_id', 'type'], ['balance']);
            VoucherTransaction::insert($voucherTransactions);

            $floatAccount->balance -= $floatDebitAmount;
            $floatAccount->save();

            return [
                'float_balance' => $floatAccount->balance
            ];
        });
    }
}
