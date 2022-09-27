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
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  StkPushPaymentFailedEvent  $event
     * @return void
     */
    public function handle(StkPushPaymentFailedEvent $event)
    {
        Log::info('...[EVENT]: STK Payment Failed...', [
            'result_description' => $event->stkCallback->result_desc,
        ]);

        MpesaEventRepository::stkPaymentFailed($event->stkCallback);
    }
}
