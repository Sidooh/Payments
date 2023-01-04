<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\TendePayEventRepository;
use DrH\TendePay\Events\TendePayRequestFailedEvent;
use Illuminate\Support\Facades\Log;

class TendePayRequestFailedListener
{
    public function handle(TendePayRequestFailedEvent $event): void
    {
        Log::info('...[EVENT]: B2B Payment Failed...', ['description' => $event->callback->status_description]);

        TendePayEventRepository::b2bPaymentFailed($event->callback);
    }
}
