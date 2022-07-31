<?php

namespace App\Helpers;

use Carbon\Carbon;

class LocalCarbon
{
    /**
     * @param string|null $timezone
     */
    public function __construct(private ?string $timezone = null) {
        $this->timezone = 'Africa/Nairobi';
    }


    public function init(): Carbon {
        return new Carbon(tz: $this->timezone);
    }

    public function today(): Carbon {
        return Carbon::today($this->timezone);
    }

    public function yesterday(): Carbon {
        return Carbon::yesterday($this->timezone);
    }

    public function now(): Carbon {
        return Carbon::now($this->timezone);
    }
}
