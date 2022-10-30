<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Services\SidoohSavings;
use App\Services\SidoohService;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Error;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaEventRepository
{
    /**
     * @throws RequestException
     */
    public static function stkPaymentFailed(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING->name) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

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


//        TODO: Move all this to payment repository?


        // TODO: If payment has no destination, mark complete and call ipn
        //Complete payment
        if (!$payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED->name]);

            SidoohService::sendCallback($payment->ipn, 'POST', [PaymentResource::make($payment)]);

            return;
        }

        $repo = new PaymentRepository(
            PaymentDTO::fromPayment($payment),
            $payment->ipn
        );

        $repo->processPayment();

        return;

//        SidoohService::sendCallback($payment->ipn, 'POST', [
//            PaymentResource::make($payment),
//            "code"    => $stkCallback->result_code,
//            "message" => $stkCallback->result_desc,
//        ]);

//
//        // TODO: If payment has destination, and no destination provider id, perform payment as necessary
//        if ($payment->destination_subtype === PaymentSubtype::VOUCHER->value) {
////            $voucherId = $payment->destination_data['voucher_id'];
////            VoucherRepository::credit($voucherId, $payment->amount, Description::VOUCHER_PURCHASE->value);
////
////            $payment->update(['status' => Status::COMPLETED->name]);
//
//            $repo = new PaymentRepository(
//                PaymentDTO::fromPayment($payment),
//                $payment->ipn
//            );
//
//            $payment = $repo->processPayment();
//
//        } elseif ($payment->destination_subtype === PaymentSubtype::FLOAT->value) {
//            $floatAccountId = $payment->destination_data['float_account_id'];
//            FloatAccountRepository::credit($floatAccountId, $payment->amount, Description::FLOAT_PURCHASE->value);
//
//            $payment->update(['status' => Status::COMPLETED->name]);
//
//        } elseif ($payment->destination_subtype === PaymentSubtype::B2B->value) {
//            $repo = new PaymentRepository(
//                PaymentDTO::fromPayment($payment),
//                $payment->ipn
//            );
//
//            $payment = $repo->processPayment();
//
//        } else {
//            throw new Exception("destination is not set");
//        }
//
//        SidoohService::sendCallback($payment->ipn, 'POST', [
//            PaymentResource::make($payment),
//            "code"    => $stkCallback->result_code,
//            "message" => $stkCallback->result_desc,
//        ]);
    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereProvider(PaymentSubtype::STK, $paymentResponse->request->id)->firstOrFail();
            if ($payment->status !== Status::PENDING->name) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::COMPLETED->name]);

            Log::info('...[REPO]: B2C Payment updated...', $payment->toArray());

            SidoohSavings::paymentCallback($payment);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    public static function b2cPaymentFailed(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereProvider(PaymentSubtype::STK, $paymentResponse->request->id)->firstOrFail();

            if ($payment->status !== Status::PENDING->name) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::FAILED->name]);

            Log::info('...[REPO]: B2C Payment updated...', $payment->toArray());

            SidoohSavings::paymentCallback($payment);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
