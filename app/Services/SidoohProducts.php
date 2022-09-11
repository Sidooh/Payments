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
    public static function findEnterprise($id)
    {
        Log::info('...[SRV - PRODUCTS]: Find Enterprise...', ['id' => $id]);

        $url = config('services.sidooh.services.products.url') . "/enterprises/$id";

        $enterprise = Cache::remember($id, (60 * 60 * 24), fn() => parent::fetch($url));

        if(!$enterprise) throw new Exception("Enterprise doesn't exist!");

        return $enterprise;
    }
}
