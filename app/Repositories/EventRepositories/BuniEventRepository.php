<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentCodes;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Services\SidoohService;
use DrH\Buni\Models\BuniStkCallback;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuniEventRepository
{
    public static function stkPaymentFailed(BuniStkCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentType::BUNI, PaymentSubtype::STK, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        $payment->update(['status' => Status::FAILED]);

        $x = (object) $payment->toArray();

        [$x->error_code, $x->error_message] = match ($callback->result_code) {
            1, '1' => [PaymentCodes::MPESA_INSUFFICIENT_BALANCE, 'Mpesa - Insufficient balance'],
            1031, 1032, '1031', '1032' => [PaymentCodes::MPESA_CANCELLED, 'Mpesa - Cancelled'],
            1037, '1037' => [PaymentCodes::MPESA_TIMEOUT, 'Mpesa - Timed out'],
            default => [PaymentCodes::MPESA_FAILED, 'Mpesa - Failed']
        };

        SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($x));
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(BuniStkCallback $callback): void
    {
        $payment = Payment::whereProvider(PaymentType::BUNI, PaymentSubtype::STK, $callback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $callback->request]);

            return;
        }

        // Complete payment
        if (! $payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED]);

            if ($payment->type === PaymentType::BUNI) $payment->type = PaymentType::MPESA;

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            return;
        }

        // Handle destination payment
        $repo = new PaymentRepository(PaymentDTO::fromPayment($payment), $payment->ipn);

        $repo->processPayment();
    }
}
