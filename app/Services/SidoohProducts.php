<?php

namespace App\Services;

use App\Enums\Status;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SidoohProducts extends SidoohService
{
    /**
     * @throws RequestException
     */
    public static function paymentCallback(int $payableId, string $payableType, Status $status)
    {
        Log::info('--- --- --- --- ---   ...[SRV - PRODUCTS]: Payment Callback...   --- --- --- --- ---');

        $url = config("services.sidooh.services.products.url") . "/callback";

        Http::retry(3)->post($url, [
            "payable_id"   => $payableId,
            "payable_type" => $payableType,
            "status"       => $status->name
        ])->throw();
    }

    /**
     * @throws RequestException
     */
    public static function requestPurchase(array $transactionIds, array $data): PromiseInterface|Response
    {
        Log::info('--- --- --- --- ---   ...[SRV - PRODUCTS]: Request Purchase...   --- --- --- --- ---');

        $url = config("services.sidooh.services.products.url") . "/purchase";

        return Http::retry(3)->post($url, [
            "transaction_ids"   => $transactionIds,
            "data" => $data
        ])->throw();
    }
}
