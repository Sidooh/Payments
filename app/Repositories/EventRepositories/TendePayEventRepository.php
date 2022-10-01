<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Models\Payment;
use App\Repositories\VoucherRepository;
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

        // TODO: refund voucher payment
        $voucherPayment = Payment::whereReference($payment->reference)
            ->whereAmount($payment->amount)
            ->whereDescription($payment->description)
            ->whereSubType(PaymentSubtype::VOUCHER)
            ->with('provider')
            ->first();

        [$voucher] = VoucherRepository::credit($voucherPayment->provider->account_id, $payment->amount, Description::VOUCHER_PURCHASE);

        $payment->update(['status' => Status::FAILED->name]);

        SidoohProducts::paymentCallback([
            'payments' => [
                [
                    ...Arr::only($payment->toArray(), ['id', 'amount', 'type', 'subtype', 'status', 'reference']),
                ],
            ],
            'credit_vouchers' => [$voucher]
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
