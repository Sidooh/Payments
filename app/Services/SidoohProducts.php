<?php

namespace App\Services;

use App\Enums\Status;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class SidoohProducts extends SidoohService
{
    /**
     * @throws RequestException
     */
    public static function paymentCallback(int $payableId, string $payableType, Status $status)
    {
        Log::info('--- --- ---   ...[SRV - PRODUCTS]: Payment Callback...   --- --- ---');

        $url = config("services.sidooh.services.products.url") . "/products/callback";

        parent::http()->post($url, [
            "payable_id"   => $payableId,
            "payable_type" => $payableType,
            "status"       => $status->name
        ])->throw();
    }

    /**
     * @throws RequestException
     */
    public static function requestPurchase(array $data): PromiseInterface|Response
    {
        Log::info('--- --- ---   ...[SRV - PRODUCTS]: Request Purchase...   --- --- ---', $data);

        $url = config("services.sidooh.services.products.url") . "/products/purchase";

        return parent::http()->post($url, $data)->throw();
    }
}
