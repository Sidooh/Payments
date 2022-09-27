<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\B2cPaymentSuccessEvent;
use Illuminate\Support\Facades\Log;

class B2CPaymentSuccess
{
    /**
     * Handle the event.
     *
     * @param  B2CPaymentSuccessEvent  $event
     * @return void
     */
    public function handle(B2CPaymentSuccessEvent $event)
    {
        Log::info('...[EVENT]: B2C Payment Sent...');

        MpesaEventRepository::b2cPaymentSent($event->mpesaBulkPaymentResponse);
    }
}
