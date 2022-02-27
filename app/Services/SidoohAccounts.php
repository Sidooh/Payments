<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SidoohAccounts
{
    private static string $url;

    public static function authenticate(): PromiseInterface|Response
    {
        $url = config('services.sidooh.services.accounts.url');

        return Http::retry(2)->post("$url/users/signin", [
            'email'    => 'aa@a.a',
            'password' => "12345678"
        ]);
    }

    /**
     * @throws Exception
     */
    public static function find($id): array
    {
        Log::info('----------------- Sidooh find Account', ['id' => $id]);

        self::$url = config('services.sidooh.services.accounts.url') . "/accounts/$id";

        $acc = Cache::remember($id, (60 * 60 * 24), fn() => self::fetch());

        if(!$acc) throw new Exception("Account doesn't exist!");

        Log::info('----------------- Sidooh find Account by id sent', ['id' => $acc['id']]);

        return $acc;
    }

    /**
     * @throws Exception
     */
    public static function findPhone($accountId)
    {
        return self::find($accountId)['phone'];
    }

    /**
     * @throws Exception
     */
    public static function findByPhone($phone)
    {
        Log::info('----------------- Sidooh find Account', ['phone' => $phone]);

        self::$url = config('services.sidooh.services.accounts.url') . "/accounts/phone/$phone";

        $acc = Cache::remember($phone, (60 * 60 * 24), function() {
            $acc = self::fetch();

            Cache::put($acc['id'], $acc);

            return $acc;
        });

        if(!$acc) throw new Exception("Account doesn't exist!");

        Log::info('----------------- Sidooh find Account by phone sent', ['id' => $acc['id']]);

        return $acc;
    }

    /**
     * @throws Exception
     */
    public static function fetch($method = "GET", $data = []): ?array
    {
        $authCookie = self::authenticate()->cookies();

        return Http::send($method, self::$url, ['cookies' => $authCookie, 'json' => $data])->throw()->json();
    }
}
