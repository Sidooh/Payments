<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SidoohAccounts extends SidoohService
{
    private static string $url;

    public static function authenticate(): PromiseInterface|Response
    {
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Authenticate...   --- --- --- --- ---');

        $url = config('services.sidooh.services.accounts.url');

        return parent::http()->post("$url/users/signin", [
            'email'    => 'aa@a.a',
            'password' => "12345678"
        ]);
    }

    /**
     * @throws Exception
     */
    public static function find($id): array
    {
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find Account...   --- --- --- --- ---', ['id' => $id]);

        self::$url = config('services.sidooh.services.accounts.url') . "/accounts/$id";

        $acc = Cache::remember($id, (60 * 60 * 24), fn() => self::fetch());

        if(!$acc) throw new Exception("Account doesn't exist!");

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
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find Account...   --- --- --- --- ---', ['phone' => $phone]);

        self::$url = config('services.sidooh.services.accounts.url') . "/accounts/phone/$phone";

        $acc = Cache::remember($phone, (60 * 60 * 24), function() {
            $acc = self::fetch();

            Cache::put($acc['id'], $acc);

            return $acc;
        });

        if(!$acc) throw new Exception("Account doesn't exist!");

        return $acc;
    }

    /**
     * @throws Exception
     */
    public static function fetch($method = "GET", $data = []): ?array
    {
        $token = Cache::remember("accounts_auth_cookie", (60), fn() => self::authenticate()["token"]);

        return parent::http()->withToken($token)->send($method, self::$url, ['json' => $data])->throw()->json();
    }
}
