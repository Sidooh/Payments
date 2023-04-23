<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\BuniEventRepository;
use DrH\Buni\Events\BuniStkRequestSuccessEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuniStkPaymentSuccess
{
    public function handle(BuniStkRequestSuccessEvent $event): void
    {
        Log::info('...[EVENT]: Buni STK Payment Received...');

        try {
            BuniEventRepository::stkPaymentReceived($event->callback);
        } catch (Exception|Throwable $e) {
            Log::critical($e);
        }
    }
}
