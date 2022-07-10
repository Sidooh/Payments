<?php

namespace App\Services;

use Exception;
use Illuminate\Auth\AuthenticationException;
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
        Log::info('...[SRV - SIDOOH]: Authenticate...');

        $url = config('services.sidooh.services.accounts.url');

        $response = Http::post("$url/users/signin", [
            'email'    => 'aa@a.a',
            'password' => "12345678"
        ]);

        if($response->successful()) return $response->json()["access_token"];

        return $response->throw()->json();
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    static function fetch(string $url, string $method = "GET", array $data = [])
    {
        Log::info('...[SRV - SIDOOH]: Fetch...', [
            'url' => $url,
            'method' => $method,
            'data'   => $data
        ]);

        $options = strtoupper($method) === "POST"
            ? ["json" => $data]
            : [];

        try {
            $t = microtime(true);
            $response = self::http()->send($method, $url, $options)->throw()->json();
            $latency = round((microtime(true) - $t) * 1000, 2);
            Log::info('...[SRV - SIDOOH]: Response... ' . $latency . 'ms', [$response]);
            return $response;
        } catch (Exception $err) {
            Log::error($err);

            if($err->getCode() === 401) throw new AuthenticationException();
        }
    }
}
