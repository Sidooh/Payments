<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\BuniEventRepository;
use DrH\Buni\Events\BuniStkRequestFailedEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuniStkPaymentFailed
{
    public function handle(BuniStkRequestFailedEvent $event): void
    {
        Log::info('...[EVENT]: Buni STK Payment Failed...', [
            'result_description' => $event->callback->result_desc,
        ]);

        try {
            BuniEventRepository::stkPaymentFailed($event->callback);
        } catch (Exception|Throwable $e) {
            Log::critical($e);
        }
    }
}
