<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
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

        if ($payment->status !== Status::PENDING->name) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

//        TODO: Refund

        $payment->update(['status' => Status::FAILED->name]);

        SidoohService::sendCallback($payment->ipn, 'POST', [
            PaymentResource::make($payment),
            "code"    => $stkCallback->result_code,
            "message" => $stkCallback->result_desc,
        ]);
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }


        //Complete payment
        if (!$payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED->name]);

            SidoohService::sendCallback($payment->ipn, 'POST', [PaymentResource::make($payment)]);

            return;
        }

        // Handle destination payment
        $repo = new PaymentRepository(
            PaymentDTO::fromPayment($payment),
            $payment->ipn
        );

        $repo->processPayment();

    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentSubtype::B2C, $paymentResponse->request->id)->firstOrFail();
            if ($payment->status !== Status::PENDING->name) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::COMPLETED->name]);

            SidoohService::sendCallback($payment->ipn, 'POST', [PaymentResource::make($payment)]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    public static function b2cPaymentFailed(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentSubtype::B2C, $paymentResponse->request->id)->firstOrFail();

            if ($payment->status !== Status::PENDING->name) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            // TODO: Refund

            $payment->update(['status' => Status::FAILED->name]);

            SidoohService::sendCallback($payment->ipn, 'POST', [PaymentResource::make($payment)]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
