<?php

namespace App\Repositories\EventRepositories;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
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
        $payment = Payment::whereDestinationProvider(PaymentType::SIDOOH, PaymentSubtype::VOUCHER, $transaction->id)->firstOrFail();

        self::processEvent($transaction, $payment);
    }

    /**
     * @throws Throwable
     */
    public static function floatTopUp(FloatAccountTransaction $transaction): void
    {
        $payment = Payment::whereDestinationProvider(PaymentType::SIDOOH, PaymentSubtype::FLOAT, $transaction->id)->firstOrFail();

        self::processEvent($transaction, $payment);
    }

    public static function processEvent(VoucherTransaction|FloatAccountTransaction $transaction, Payment $payment): void
    {
        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $transaction]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED]);

        if ($payment->subtype === PaymentSubtype::STK) {
            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        }
    }
}
