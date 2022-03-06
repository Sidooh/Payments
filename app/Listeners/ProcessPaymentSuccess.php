<?php

namespace App\Listeners;

use App\Events\PaymentSuccessEvent;
use App\Services\SidoohProducts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessPaymentSuccess implements ShouldQueue
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
     * @param \App\Events\PaymentSuccessEvent $event
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handle(PaymentSuccessEvent $event)
    {
        Log::info('--- --- --- --- ---   ...[EVENT]: Process Successful Payment...   --- --- --- --- ---');

        $response = SidoohProducts::requestPurchase($event->transactions, $event->data);

        Log::info($response);
    }
}
