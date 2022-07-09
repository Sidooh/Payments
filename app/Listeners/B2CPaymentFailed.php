<?php

namespace App\Listeners;

use App\Helpers\SidoohNotify\EventTypes;
use App\Repositories\EventRepositories\MpesaEventRepository;
use App\Repositories\NotificationRepository;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use Illuminate\Support\Facades\Log;

class B2CPaymentFailed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param B2CPaymentFailedEvent $event
     * @return void
     */
    public function handle(B2CPaymentFailedEvent $event)
    {
        Log::info('...[EVENT]: B2C Payment Failed...', [
            "result_description" => $event->mpesaBulkPaymentResponse->result_desc
        ]);

        MpesaEventRepository::b2cPaymentFailed($event->mpesaBulkPaymentResponse);

//        $b2c = $event->bulkPaymentResponse; //an instance of mpesa callback model
//
//        Log::info('----------------- B2C Payment Failed (' . $b2c->ResultDesc . ')');
//
//        $payment = Payment::wherePaymentId($event->bulkPaymentResponse->request->id)->whereSubtype('B2C')->firstOrFail();
//        $transaction = $payment->payable;
//        $account = $transaction->account;
//
//        $message = "Sorry! We failed to complete your withdrawal transaction. No amount was deducted from your account. We apologize for the inconvenience. Please try again.";
//
//        NotificationRepository::sendSMS([$account->phone], $message, EventTypes::WITHDRAWAL_FAILURE);
    }
}
