<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SidoohAccounts extends SidoohService
{
    /**
     * @throws Exception
     */
    static function find($id): array
    {
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find...   --- --- --- --- ---', ['id' => $id]);

        $url = config('services.sidooh.services.accounts.url') . "/accounts/$id";

        $acc = Cache::remember($id, (60 * 60 * 24), fn() => parent::http()->get($url)->json());

        if(!$acc) throw new Exception("Account doesn't exist!");

        return $acc;
    }

    /**
     * @throws Exception
     */
    public static function findByPhone($phone)
    {
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find By Phone...   --- --- --- --- ---', ['phone' => $phone]);

        $url = config('services.sidooh.services.accounts.url') . "/accounts/phone/$phone";

        $acc = Cache::remember($phone, now()->addDay(), fn() => parent::fetch($url));

        if(!$acc) throw new Exception("Account doesn't exist!");

        return $acc;
    }
}
