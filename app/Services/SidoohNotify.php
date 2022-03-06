<?php

namespace App\Services;

use App\Enums\EventType;
use Illuminate\Support\Facades\Log;

class SidoohNotify
{
    public static function notify(array $to, string $message, EventType $eventType): array
    {
        Log::info('--- --- --- --- ---   ...[SRV - NOTIFY]: Send Notification...   --- --- --- --- ---', [
            "channel"     => "sms",
            "event_type"  => $eventType->value,
            "destination" => implode(', ', $to),
            "content"     => $message
        ]);

//        $url = config('services.sidooh.services.notify.url');
//
//        $response = Http::retry(3)->post($url, [
//            "channel"     => "sms",
//            "event_type"  => $eventType->value,
//            "destination" => $to,
//            "content"     => $message
//        ]);
//
//        Log::info('--- --- --- --- ---   ...SRV - NOTIFY: Notification Sent...   --- --- --- --- ---', [
//            'id' => $response->json()['id']
//        ]);
//
//        return $response->json();

        return [];
    }
}
