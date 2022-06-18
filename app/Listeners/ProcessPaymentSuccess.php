<?php

namespace App\Listeners;

use App\Events\PaymentSuccessEvent;
use App\Services\SidoohProducts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class ProcessPaymentSuccess
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
     * @param PaymentSuccessEvent $event
     * @return void
     * @throws RequestException
     */
    public function handle(PaymentSuccessEvent $event): void
    {
        Log::info('...[EVENT]: Process Payment Success...');

        $response = SidoohProducts::requestPurchase($event->transactions, $event->data);

        Log::info($response);
    }
}
