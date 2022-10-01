<?php

namespace App\Services;

use App\Models\Payment;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class SidoohSavings extends SidoohService
{
    /**
     * @throws RequestException
     */
    public static function paymentCallback(Payment $payment): PromiseInterface|Response
    {
        Log::info('...[SRV - Savings]: Payment Callback...');

        $url = config('services.sidooh.services.savings.url').'/payments/callback';

        return parent::fetch($url, 'POST', $payment->toArray())->throw();
    }
}
