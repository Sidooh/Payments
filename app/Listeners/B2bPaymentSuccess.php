<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\B2bPaymentSuccessEvent;
use Illuminate\Support\Facades\Log;

class B2bPaymentSuccess
{
    /**
     * Handle the event.
     *
     * @param B2bPaymentSuccessEvent $event
     * @return void
     */
    public function handle(B2bPaymentSuccessEvent $event): void
    {
        Log::info('...[EVENT]: B2B Payment Sent...');

        MpesaEventRepository::b2bPaymentSent($event->mpesaB2bCallback);
    }
}
