<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\SidoohRepositories\VoucherRepository;
use App\Services\SidoohService;
use DrH\TendePay\Models\TendePayCallback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TendePayEventRepository
{
    /**
     * @throws Throwable
     */
    public static function b2bPaymentFailed(TendePayCallback $callback): void
    {
        $payment = Payment::whereDestinationProvider(PaymentSubtype::B2B, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        // TODO: What if float was used? can it be used?
        DB::transaction(function() use ($payment) {
            VoucherRepository::creditDefaultVoucherForAccount($payment->account_id, $payment->amount, Description::VOUCHER_REFUND->value);

            $payment->update(['status' => Status::FAILED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        });
    }

    public static function b2bPaymentSent(TendePayCallback $callback): void
    {
        $payment = Payment::whereDestinationProvider(PaymentSubtype::B2B, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED]);

        $data = PaymentResource::make($payment);
        $data['mpesa_code'] = $callback->confirmation_code;

        SidoohService::sendCallback($payment->ipn, 'POST', $data);
    }
}
