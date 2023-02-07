<?php

namespace App\Services;

use App\Enums\EventType;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;

class SidoohNotify extends SidoohService
{
    public static function notify(array|string|int $to, string $message, EventType $eventType)
    {
        Log::info('...[SRV - NOTIFY]: Send Notification...', [
            'channel'     => 'SMS',
            'event_type'  => $eventType->value,
            'destination' => is_array($to) ? implode(', ', $to) : $to,
            'content'     => $message,
        ]);

        $url = config('services.sidooh.services.notify.url').'/notifications';

        try {
            $response = parent::fetch($url, 'POST', [
                'channel'     => 'sms',
                'event_type'  => $eventType->value,
                'destination' => $to,
                'content'     => $message,
            ]);

            // TODO: To implement if necessary
//            Notification::create([
//                'to' => $to,
//                'message' => $message,
//                'event' => $eventType,
//                'response' => $response
//            ]);
        } catch (Exception|Error $e) {
//            Notification::create([
//                'to' => $to,
//                'message' => $message,
//                'event' => $eventType,
//                'response' => ["err" => $e->getMessage()]
//            ]);
        }
    }
}
