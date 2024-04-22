<?php

namespace App\Listeners;

use DrH\Jenga\Events\JengaIpnEvent;
use Illuminate\Support\Facades\Log;

class JengaIpnHandler
{
    public function handle(JengaIpnEvent $event): void
    {
        Log::info('...[EVENT]: Jenga IPN Received... -- disabled');

//        try {
//            JengaEventRepository::ipnReceived($event->ipn);
//        } catch (Exception|Throwable $e) {
//            Log::critical($e);
//        }
    }
}
