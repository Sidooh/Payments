<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\EventType;
use App\Enums\PaymentCodes;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\FloatAccount;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use App\Services\SidoohService;
use DrH\Buni\Models\BuniIpn;
use DrH\Buni\Models\BuniStkCallback;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuniEventRepository
{
    public static function stkPaymentFailed(BuniStkCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentType::BUNI, PaymentSubtype::STK, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::FAILED]);

        $x = (object) $payment->toArray();

        [$x->error_code, $x->error_message] = match ($callback->result_code) {
            1, '1' => [PaymentCodes::MPESA_INSUFFICIENT_BALANCE, 'Mpesa - Insufficient balance'],
            1031, 1032, '1031', '1032' => [PaymentCodes::MPESA_CANCELLED, 'Mpesa - Cancelled'],
            1037, '1037' => [PaymentCodes::MPESA_TIMEOUT, 'Mpesa - Timed out'],
            default => [PaymentCodes::MPESA_FAILED, 'Mpesa - Failed']
        };

        SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($x));
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(BuniStkCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentType::BUNI, PaymentSubtype::STK, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        // Complete payment
        if (!$payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            return;
        }

        // Handle destination payment
        $repo = new PaymentRepository(PaymentDTO::fromPayment($payment), $payment->ipn);

        $repo->processPayment();
    }


    /**
     * @throws Throwable
     */
    public static function ipnReceived(BuniIpn $ipn): void
    {
        $reference = $ipn->customer_reference;
        if ($ipn->status === 'COMPLETED') {
            Log::error('ipn already completed', $ipn->id);
            return;
        }

        if ($ipn->narration === 'ATM Cash KCB') {
            $values = explode(" ", $ipn->customer_reference);
            $reference = substr_replace($values[1], '', 0, 7);

            self::creditAccount($ipn, $reference, $values) ;

            return;
        }

        if ($ipn->narration === 'Ag Dpst') {
            $values = explode(" ", $ipn->customer_reference);
            foreach ($values as $value) {
                if (is_numeric($value)) {
                    $reference = $value;
                    break;
                }
            }

            self::creditAccount($ipn, $reference, $values) ;

        }
    }

    private static function creditAccount(BuniIpn $ipn, string $reference, $values): void
    {
        if (!is_numeric($reference)) {
            Log::error('reference retrieved is invalid', $values);
            return;
        }

        $amount = $ipn->transaction_amount;

        // find float account with reference
        $float = FloatAccount::whereFloatableType("MERCHANT")->whereDescription($reference)->first();
        FloatAccountRepository::credit($float->id, $amount, "Account credit: KCB - $ipn->transaction_reference", 0, ["buni_ipn_id" => $ipn->id]);
        $float->refresh();

        $ipn->status = 'COMPLETED';
        $ipn->save();


        $amount = 'Ksh'.number_format($amount, 2);
        $balance = 'Ksh'.number_format($float->balance, 2);
        $date = $float->updated_at->timezone('Africa/Nairobi')->format(config('settings.sms_date_time_format'));

        $message = "$amount has been added to your merchant voucher account on $date via KCB.\n";
        $message .= "New balance is $balance.";

        $account = SidoohAccounts::find($float->account_id);
        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);
    }
}
