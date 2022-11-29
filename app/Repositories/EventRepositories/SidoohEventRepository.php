<?php

namespace App\Repositories\EventRepositories;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\FloatAccountTransaction;
use App\Models\Payment;
use App\Models\VoucherTransaction;
use App\Services\SidoohService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SidoohEventRepository
{
    /**
     * @throws Throwable
     */
    public static function voucherCredited(VoucherTransaction $transaction): void
    {
        $payment = Payment::whereDestinationProvider(PaymentSubtype::VOUCHER, $transaction->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::critical('Payment is not pending...', [$payment, $transaction]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED->name]);

        if ($payment->subtype === PaymentSubtype::STK->name) {
            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        }
    }

    /**
     * @throws Throwable
     */
    public static function floatTopup(FloatAccountTransaction $transaction): void
    {
        $payment = Payment::whereDestinationProvider(PaymentSubtype::FLOAT, $transaction->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::critical('Payment is not pending...', [$payment, $transaction]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED->name]);

        SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
    }
}
