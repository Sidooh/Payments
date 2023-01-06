<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Events\B2cPaymentFailedEvent;
use Illuminate\Support\Facades\Log;

class B2CPaymentFailed
{
    /**
     * Handle the event.
     *
     * @param  B2CPaymentFailedEvent  $event
     * @return void
     */
    public function handle(B2CPaymentFailedEvent $event): void
    {
        Log::info('...[EVENT]: B2C Payment Failed...', [
            'result_description' => $event->mpesaBulkPaymentResponse->result_desc,
        ]);

        MpesaEventRepository::b2cPaymentFailed($event->mpesaBulkPaymentResponse);
    }
}
