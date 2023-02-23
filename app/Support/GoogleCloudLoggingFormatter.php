<?php

namespace App\Support;

use DateTimeInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class GoogleCloudLoggingFormatter extends JsonFormatter
{
    public function normalizeRecord(LogRecord $record): array
    {
        // Re-key level for GCP logging
        $record['severity'] = $record['level_name'];
        $record['timestamp'] = $record['datetime']->format(DateTimeInterface::RFC3339_EXTENDED);

        // Remove keys that are not used by GCP
        unset($record['level'], $record['level_name'], $record['datetime']);

        return parent::normalizeRecord($record);
    }
}
