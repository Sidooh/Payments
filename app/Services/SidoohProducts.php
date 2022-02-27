<?php

namespace App\Services;

use App\Enums\Status;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SidoohProducts
{
    /**
     * @throws RequestException
     */
    public static function paymentCallback(int $payableId, string $payableType, Status $status)
    {
        Log::alert('****************************    SIDOOH-SRV PRODUCTS: Payment Callback     ****************************');

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
    public static function requestPurchase(int $transactionId, array $data): PromiseInterface|Response
    {
        Log::alert('****************************    SIDOOH-SRV PRODUCTS: Request Purchase     ****************************');

        $url = config("services.sidooh.services.products.url") . "/purchase";

        return Http::retry(3)->post($url, [
            "transaction_id"   => $transactionId,
            "data" => $data
        ])->throw();
    }
}
