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
    public static function getAll(): array
    {
        Log::info('...[SRV - ACCOUNTS]: Get All...');

        $url = config('services.sidooh.services.accounts.url').'/accounts?with_user=true';

        return Cache::remember('all_accounts', (60 * 60 * 24), function() use ($url) {
            $accounts = parent::fetch($url, log: false) ?? [];

            foreach ($accounts as $acc) {
                Cache::put($acc['id'], $acc, (60 * 60 * 24));
            }

            return $accounts;
        });
    }

    /**
     * @throws Exception
     */
    public static function find(int|string $id): array
    {
        Log::info('...[SRV - ACCOUNTS]: Find...', ['id' => $id]);

        $url = config('services.sidooh.services.accounts.url')."/accounts/$id?with_user=true";

        $acc = Cache::remember("account_$id", (60 * 60 * 24), fn () => parent::fetch($url));

        if (! $acc) {
            throw new Exception("Account doesn't exist!");
        }

        return $acc;
    }

    /**
     * @throws Exception
     */
    public static function findByPhone(int|string $phone)
    {
        Log::info('...[SRV - ACCOUNTS]: Find By Phone...', ['phone' => $phone]);

        $url = config('services.sidooh.services.accounts.url')."/accounts/phone/$phone";

        $acc = Cache::remember("account_$phone", (60 * 60 * 24), fn () => parent::fetch($url));

        if (! $acc) {
            throw new Exception("Account doesn't exist!");
        }

        return $acc;
    }
}
