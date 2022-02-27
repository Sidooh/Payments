<?php

namespace App\Services;

use App\Enums\EventType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SidoohNotify
{
    public static function notify(array $to, string $message, EventType $eventType): array
    {
//        Log::info('----------------- Sidooh SMS Notification', [
//            'eventType' => $eventType,
//            'to'        => $to,
//            'message'   => $message
//        ]);
//        $url = env('SIDOOH_NOTIFY_URL');

        Log::alert('****************************    SIDOOH NOTIFY     ****************************');
        Log::alert('******************************************************************************');

        Log::info([
            "channel"     => "sms",
            "event_type"  => $eventType->value,
            "destination" => implode(', ', $to),
            "content"     => $message
        ]);

        return [];

        $response = Http::retry(3)->post($url, [
            "channel"     => "sms",
            "event_type"  => $eventType->value,
            "destination" => $to,
            "content"     => $message
        ]);

        Log::info('----------------- Sidooh SMS Notification sent', [
            'id' => $response->json()['id']
        ]);

        return $response->json();
    }
}
