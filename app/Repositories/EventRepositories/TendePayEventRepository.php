<?php

namespace App\Repositories\EventRepositories;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Models\Payment;
use App\Services\SidoohProducts;
use DrH\TendePay\Models\TendePayCallback;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class TendePayEventRepository
{
    public static function b2bPaymentFailed(TendePayCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::B2B, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::FAILED->name]);

        SidoohProducts::paymentCallback([
            'payments' => [
                [
                    ...Arr::only($payment->toArray(), ['id', 'amount', 'type', 'subtype', 'status', 'reference']),
                ],
            ],
        ]);
    }

    public static function b2bPaymentSent(TendePayCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED->name]);

        Log::info('...[REP - MPESA]: Payment updated...', [$payment->id, $payment->status]);

        $data['payments'] = [
            Arr::only($payment->toArray(), ['id', 'amount', 'type', 'subtype', 'status', 'reference']),
        ];

        SidoohProducts::paymentCallback($data);
    }
}
