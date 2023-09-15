<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\B2bPaymentFailedEvent;
use Illuminate\Support\Facades\Log;

class B2bPaymentFailed
{
    /**
     * Handle the event.
     *
     * @param B2bPaymentFailedEvent $event
     * @return void
     *
     * @throws \Throwable
     */
    public function handle(B2BPaymentFailedEvent $event): void
    {
        Log::info('...[EVENT]: B2B Payment Failed...', [
            'result_description' => $event->mpesaB2bCallback->result_desc,
        ]);

        MpesaEventRepository::b2bPaymentFailed($event->mpesaB2bCallback);
    }
}
