<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SidoohProducts extends SidoohService
{
    public static function baseUrl()
    {
        return config('services.sidooh.services.products.url');
    }

    /**
     * @throws \Exception
     */
    public static function findEnterprise($id)
    {
        Log::info('...[SRV - PRODUCTS]: Find Enterprise...', ['id' => $id]);

        $url = self::baseUrl() . "/enterprises/$id?with=enterprise_accounts";

        $response = Cache::remember($id, (60 * 60 * 24), fn() => parent::fetch($url));

        if (!$response) {
            throw new Exception("Enterprise doesn't exist!");
        }

        return $response;
    }
}
