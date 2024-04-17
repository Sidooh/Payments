<?php

namespace App\Repositories\EventRepositories;

use App\Enums\EventType;
use App\Models\FloatAccount;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use DrH\Jenga\Models\JengaBillIpn;
use Illuminate\Support\Facades\Log;
use Throwable;

class JengaEventRepository
{

    /**
     * @throws Throwable
     */
    public static function billIpnReceived(JengaBillIpn $ipn): void
    {
        if ($ipn->status === 'COMPLETED') {
            Log::error('ipn already completed', $ipn->id);
            return;
        }

        if (!is_numeric($ipn->reference)) {
            Log::error('reference retrieved is invalid', $ipn->reference);
            return;
        }

        return;

        $amount = $ipn->bill_amount;

        // find float account with reference
        $float = FloatAccount::whereFloatableType("MERCHANT")->whereDescription($reference)->first();
        FloatAccountRepository::credit($float->id, $amount, "Account Credit: Equity Bill - $ipn->bankreference", 0, ["jenga_bill_ipn_id" => $ipn->id]);
        $float->refresh();

        $ipn->status = 'COMPLETED';
        $ipn->save();


        $amount = 'Ksh'.number_format($amount, 2);
        $balance = 'Ksh'.number_format($float->balance, 2);

        $message = "Your merchant voucher has been credited with $amount.\n";
        $message .= "New balance is $balance.";

        $account = SidoohAccounts::find($float->account_id);
        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);
    }
}
