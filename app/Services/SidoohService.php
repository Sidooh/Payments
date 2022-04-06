<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SidoohService
{
    public static function http(): PendingRequest
    {
        return Http::/*retry(1)->*/acceptJson();
    }
}
