<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SidoohProducts extends SidoohService
{
    /**
     * @throws RequestException
     */
    public static function paymentCallback(array $data): PromiseInterface|Response
    {
        Log::info('...[SRV - PRODUCTS]: Payment Callback...', $data);

        $url = config("services.sidooh.services.products.url") . "/payments/callback";

        return parent::http()->post($url, $data)->throw();
    }

    /**
     * @throws \Exception
     */
    public static function findEnterprise($id, $with = [])
    {
        Log::info('...[SRV - PRODUCTS]: Find Enterprise...', ['id' => $id]);

        $url = config('services.sidooh.services.products.url') . "/enterprises/$id";

        if(in_array("enterprise_accounts", $with)) $url .= "?with=enterprise_accounts";

        $response = Cache::remember($id, (60 * 60 * 24), fn() => parent::fetch($url));

        if(!$response) throw new Exception("Enterprise doesn't exist!");

        return $response;
    }
}
