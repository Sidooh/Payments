<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\JengaEventRepository;
use DrH\Jenga\Events\JengaIpnEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class JengaIpnHandler
{
    public function handle(JengaIpnEvent $event): void
    {
        Log::info('...[EVENT]: Jenga IPN Received...');

        try {
            JengaEventRepository::ipnReceived($event->ipn);
        } catch (Exception|Throwable $e) {
            Log::critical($e);
        }
    }
}
