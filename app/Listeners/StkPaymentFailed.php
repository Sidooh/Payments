<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\StkPushPaymentFailedEvent;
use Illuminate\Support\Facades\Log;

class StkPaymentFailed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() { }

    /**
     * Handle the event.
     *
     * @param StkPushPaymentFailedEvent $event
     * @return void
     */
    public function handle(StkPushPaymentFailedEvent $event)
    {
        Log::info("----------------- STK Payment Failed ({$event->stkCallback->ResultDesc})");

        MpesaEventRepository::stkPaymentFailed($event->stkCallback);
    }
}
