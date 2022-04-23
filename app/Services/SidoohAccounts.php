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
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find Account...   --- --- --- --- ---', ['id' => $id]);

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
        Log::info('--- --- --- --- ---   ...[SRV - ACCOUNTS]: Find Account...   --- --- --- --- ---', ['phone' => $phone]);

        $url = config('services.sidooh.services.accounts.url') . "/accounts/phone/$phone";

        $acc = Cache::remember($phone, (60 * 60 * 24), function() use ($url) {
            $acc = parent::http()->get($url)->json();

            Cache::put($acc['id'], $acc);

            return $acc;
        });

        if(!$acc) throw new Exception("Account doesn't exist!");

        return $acc;
    }
}
