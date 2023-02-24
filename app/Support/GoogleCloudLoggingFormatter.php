<?php

namespace App\Support;

use DateTimeInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class GoogleCloudLoggingFormatter extends JsonFormatter
{
    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        // Re-key level for GCP logging
        $normalized['severity'] = $normalized['level_name'];
        $normalized['times'] = $record->datetime->format(DateTimeInterface::RFC3339_EXTENDED);

        // Remove keys that are not used by GCP
        unset($normalized['level'], $normalized['level_name'], $normalized['datetime']);

//        ddj($normalized);

        return $normalized;
    }
}
