<?php

namespace App\Services;

use App\Enums\EventType;
use Illuminate\Support\Facades\Log;

class SidoohNotify extends SidoohService
{
    public static function notify(array $to, string $message, EventType $eventType)
    {
        Log::info('...[SRV - NOTIFY]: Send Notification...', [
            "channel"     => "sms",
            "event_type"  => $eventType->value,
            "destination" => implode(', ', $to),
            "content"     => $message
        ]);

        $url = config('services.sidooh.services.notify.url') . "/notifications";

        $response = parent::http()->post($url, [
            "channel"     => "sms",
            "event_type"  => $eventType->value,
            "destination" => $to,
            "content"     => $message
        ])->json();

        Log::info('...[SRV - NOTIFY]: Notification Sent...', $response);

        return $response;
    }
}
