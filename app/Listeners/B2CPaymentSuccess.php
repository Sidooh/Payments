<?php

namespace App\Listeners;

use App\Helpers\SidoohNotify\EventTypes;
use App\Models\Earning;
use App\Repositories\EventRepositories\MpesaEventRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TransactionRepository;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use Illuminate\Support\Facades\Log;

class B2CPaymentSuccess
{


    /**
     * Handle the event.
     *
     * @param B2CPaymentSuccessEvent $event
     * @return void
     */
    public function handle(B2CPaymentSuccessEvent $event)
    {
        Log::info('...[EVENT]: B2C Payment Sent...');

        MpesaEventRepository::b2cPaymentSent($event->mpesaBulkPaymentResponse);
//        Log::info($event->bulkPaymentResponse);
//        Log::info($event->bulkPaymentResponse->request);

//        $payment = Payment::wherePaymentId($event->mpesaBulkPaymentResponse->request->id)
//            ->whereSubtype(PaymentSubtype::B2C)
//            ->firstOrFail();
//        $transaction = $payment->payable;
//        $account = $transaction->account;
//
//        $transactionCost = 30;
//
//        $cAcc = $account->current_account;
//        $cAcc->out += ($transaction->amount + $transactionCost);
//        $cAcc->save();
//
//        $e = Earning::create([
//            'transaction_id' => $transaction->id,
//            'earnings' => $transactionCost,
//            'type' => 'SYSTEM',
//        ]);
//
//        if ($payment->status == 'Complete')
//            return;
//
//        $payment->status = 'Complete';
//        $payment->save();
//
//        (new TransactionRepository())->updateStatus($transaction, 'completed');
//
//
//        $other_phone = explode(" - ", $event->bulkPaymentResponse->request->remarks);
//        $amount = (int)$event->bulkPaymentResponse->data->TransactionAmount;
//        $date = $event->bulkPaymentResponse->updated_at->timezone('Africa/Nairobi')->format(config("settings.sms_date_time_format"));
//        $code = config('services.at.ussd.code');
//
//        if ($payment->subtype == 'VOUCHER') {
//            $bal = $account->voucher->balance;
////            $vtext = " New Voucher balance is KES$bal.";
//            $method = 'VOUCHER';
//        } elseif ($payment->type == 'MPESA') {
//            $method = 'MPESA';
////            $vtext = '';
//        }
//
//        $cbal = $account->current_account->balance;
//
//        if (count($other_phone) > 1) {
//            $message = "You have redeemed KES{$amount} for $method {$other_phone[1]} from your Sidooh account on {$date}. Your current account balance is $cbal.";
//
//            NotificationRepository::sendSMS([$account->phone], $message, EventTypes::WITHDRAWAL_PAYMENT);
//
//            $message = "Congratulations! You have received $method KES{$amount} from Sidooh account {$account->phone} on {$date}. Sidooh Makes You Money with Every Purchase.\n\nDial $code NOW for FREE on your Safaricom line to BUY AIRTIME & START EARNING from your purchases.";
//
//            NotificationRepository::sendSMS([$other_phone[1]], $message, EventTypes::WITHDRAWAL_PAYMENT);
//        } else {
//            $message = "You have redeemed KES{$amount} from your Sidooh account on {$date} to $method. Your current account balance is $cbal.";
//
//            NotificationRepository::sendSMS([$account->phone], $message, EventTypes::WITHDRAWAL_PAYMENT);
//        }

    }
}
