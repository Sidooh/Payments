<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\Description;
use App\Enums\PaymentCodes;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Services\SidoohService;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaEventRepository
{
    public static function stkPaymentFailed(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

        $payment->update(['status' => Status::FAILED]);

        $x = (object) $payment->toArray();

        [$x->error_code, $x->error_message] = match ($stkCallback->result_code) {
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
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

        //Complete payment
        if (! $payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            return;
        }

        // Handle destination payment
        $repo = new PaymentRepository(PaymentDTO::fromPayment($payment), $payment->ipn);

        $repo->processPayment();
    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentSubtype::B2C, $paymentResponse->request->id)
                              ->firstOrFail();
            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @throws \Throwable
     */
    public static function b2cPaymentFailed(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentSubtype::B2C, $paymentResponse->request->id)
                              ->firstOrFail();

            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $account = $payment->provider->floatAccount;

            FloatAccountRepository::credit(
                $account->id,
                $payment->amount,
                Description::ACCOUNT_WITHDRAWAL_REFUND->value,
                $payment->charge
            );

            $payment->update(['status' => Status::FAILED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
