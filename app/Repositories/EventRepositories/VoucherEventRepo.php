<?php

namespace App\Repositories\EventRepositories;

use App\Enums\EventType;
use App\Models\Voucher;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use Exception;

class VoucherEventRepo extends EventRepository
{
    /**
     * @param Voucher $voucher
     * @param         $amount
     * @throws Exception
     */
    public static function voucherPurchaseSuccess(Voucher $voucher, $amount)
    {
        $account = SidoohAccounts::find($voucher->account_id);

        $phone = ltrim($account['phone'], '+');

        $date = $voucher->updated_at->timezone('Africa/Nairobi')->format(config("settings.sms_date_time_format"));

        $message = "Congratulations! You have successfully purchased a voucher ";
        $message .= "worth Ksh{$amount} on {$date}.\n\n";
        $message .= config('services.sidooh.tagline');

        SidoohNotify::notify([$phone], $message, EventType::VOUCHER_PURCHASE);
    }
}
