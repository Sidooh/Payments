<?php

namespace App\Logging;

use Monolog\Logger;

class CreateGCPLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        return new Logger('gcp');
    }
}
