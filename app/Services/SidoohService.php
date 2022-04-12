<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SidoohService
{
    public static function http(): PendingRequest
    {
        $token = Cache::remember("auth_token", (60 * 14), fn() => self::authenticate());

        return Http::withToken($token)->/*retry(1)->*/acceptJson();
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    static function authenticate()
    {
        Log::info('--- --- --- --- ---   ...[SRV - SIDOOH]: Authenticate...   --- --- --- --- ---');

        $url = config('services.sidooh.services.accounts.url');

        $response = Http::post("$url/users/signin", [
            'email'    => 'aa@a.a',
            'password' => "12345678"
        ]);

        if($response->successful()) return $response->json()["token"];

        return $response->throw()->json();
    }
}
