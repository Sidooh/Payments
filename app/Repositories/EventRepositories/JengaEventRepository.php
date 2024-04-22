<?php

namespace App\Repositories\EventRepositories;

use App\Enums\EventType;
use App\Models\FloatAccount;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use DrH\Jenga\Models\JengaBillIpn;
use DrH\Jenga\Models\JengaIpn;
use Illuminate\Support\Facades\Log;
use Throwable;

class JengaEventRepository
{
    /**
     * @throws Throwable
     */
    public static function ipnReceived(JengaIpn $ipn): void
    {
        // TODO: implement this to avoid redundancies
        if ($ipn->status === 'COMPLETED') {
            Log::error('ipn already completed', $ipn->id);
            return;
        }

        if (!is_numeric($ipn->transaction_bill_number)) {
            Log::error('reference retrieved is invalid', $ipn->transaction_bill_number);
            return;
        }

        $amount = $ipn->transaction_amount;

        // find float account with reference
        $float = FloatAccount::whereFloatableType("MERCHANT")->whereDescription($ipn->transaction_bill_number)->firstOrFail();
        FloatAccountRepository::credit($float->id, $amount, "Account Credit: Equity - $ipn->transaction_additional_info", 0, ["jenga_ipn_id" => $ipn->id]);
        $float->refresh();

        $amount = 'Ksh'.number_format($amount, 2);
        $balance = 'Ksh'.number_format($float->balance, 2);

        $message = "Your merchant voucher has been credited with $amount.\n";
        $message .= "New balance is $balance.";

        $account = SidoohAccounts::find($float->account_id);
        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);
    }


    /**
     * @throws Throwable
     */
    public static function billIpnReceived(JengaBillIpn $ipn): void
    {
        // TODO: implement this to avoid redundancies
        if ($ipn->status === 'COMPLETED') {
            Log::error('ipn already completed', $ipn->id);
            return;
        }

        if (!is_numeric($ipn->bill_number)) {
            Log::error('reference retrieved is invalid', $ipn->bill_number);
            return;
        }

        $amount = $ipn->bill_amount;

        // find float account with reference
        $float = FloatAccount::whereFloatableType("MERCHANT")->whereDescription($ipn->bill_number)->firstOrFail();
        FloatAccountRepository::credit($float->id, $amount, "Account Credit: Equity Bill - $ipn->bankreference", 0, ["jenga_bill_ipn_id" => $ipn->id]);
        $float->refresh();

        $amount = 'Ksh'.number_format($amount, 2);
        $balance = 'Ksh'.number_format($float->balance, 2);

        $message = "Your merchant voucher has been credited with $amount.\n";
        $message .= "New balance is $balance.";

        $account = SidoohAccounts::find($float->account_id);
        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);
    }
}
