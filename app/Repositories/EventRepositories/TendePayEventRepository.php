<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Enums\VoucherType;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Voucher;
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

        if ($payment->status !== Status::PENDING->name) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        // TODO: What if float was used? can it be used?
        DB::transaction(function() use ($payment) {
            $voucher = Voucher::firstOrCreate([
                'account_id' => $payment->account_id,
                'type'       => VoucherType::SIDOOH,
            ]);

            VoucherRepository::credit($voucher->id, $payment->amount, Description::VOUCHER_REFUND->value);

            $payment->update(['status' => Status::FAILED->name]);

            SidoohService::sendCallback($payment->ipn, 'POST', [
                PaymentResource::make($payment),
                "message" => "Merchant payment failed",
            ]);
        });
    }

    public static function b2bPaymentSent(TendePayCallback $callback): void
    {
        $payment = Payment::whereDestinationProvider(PaymentSubtype::B2B, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::error('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::COMPLETED->name]);

        SidoohService::sendCallback($payment->ipn, 'POST', [PaymentResource::make($payment)]);
    }
}
