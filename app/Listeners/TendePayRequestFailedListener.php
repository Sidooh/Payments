<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\TendePayEventRepository;
use DrH\TendePay\Events\TendePayRequestFailedEvent;
use Illuminate\Support\Facades\Log;

class TendePayRequestFailedListener
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
     * @return void
     */
    public function handle(TendePayRequestFailedEvent $event)
    {
        //
        Log::info('...[EVENT]: B2B Payment Failed...', ['description' => $event->callback->status_description]);

        TendePayEventRepository::b2bPaymentFailed($event->callback);
    }
}
