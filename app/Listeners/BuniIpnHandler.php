<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\BuniEventRepository;
use DrH\Buni\Events\BuniIpnEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuniIpnHandler
{
    public function handle(BuniIpnEvent $event): void
    {
        Log::info('...[EVENT]: Buni IPN Received...');

        try {
            BuniEventRepository::ipnReceived($event->ipn);
        } catch (Exception|Throwable $e) {
            Log::critical($e);
        }
    }
}
