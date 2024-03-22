<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\TendePayEventRepository;
use DrH\TendePay\Events\TendePayRequestSuccessEvent;
use Illuminate\Support\Facades\Log;

class TendePayRequestSuccessListener
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
     */
    public function handle(TendePayRequestSuccessEvent $event): void
    {
        Log::info('...[EVENT]: B2B Payment Completed...');

        TendePayEventRepository::b2bPaymentSent($event->callback);
    }
}
