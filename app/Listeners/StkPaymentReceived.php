<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\StkPushPaymentSuccessEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class StkPaymentReceived
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  StkPushPaymentSuccessEvent  $event
     * @return void
     *
     * @throws Throwable
     */
    public function handle(StkPushPaymentSuccessEvent $event): void
    {
        Log::info('...[EVENT]: STK Payment Received...');

        try {
            MpesaEventRepository::stkPaymentReceived($event->stkCallback);
        } catch (Exception $e) {
            Log::critical($e);
        }
    }
}
