<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\JengaEventRepository;
use DrH\Jenga\Events\JengaBillIpnEvent;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class JengaBillIpnHandler
{
    public function handle(JengaBillIpnEvent $event): void
    {
        Log::info('...[EVENT]: Jenga Bill IPN Received...');

        try {
            JengaEventRepository::billIpnReceived($event->ipn);
        } catch (Exception|Throwable $e) {
            Log::critical($e);
        }
    }
}
