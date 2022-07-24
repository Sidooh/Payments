<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\MpesaReference;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Models\Payment;
use App\Repositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohProducts;
use App\Services\SidoohSavings;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaEventRepository extends EventRepository
{
    /**
     * @throws RequestException
     */
    public static function stkPaymentFailed(MpesaStkCallback $stkCallback)
    {
        // TODO: Make into a transaction/try catch?
//        $payment = Payment::whereProviderId($stkCallback->request->id)
//            ->whereSubtype(PaymentSubtype::STK->name)
//            ->firstOrFail();

        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();
        if ($payment->status !== Status::PENDING->name) throw new \Error("Payment is not pending... - $payment->id");

        $payment->update(["status" => Status::FAILED->name]);

        SidoohProducts::paymentCallback([
            "payments" => [
                ...Arr::only(
                    $payment->toArray(),
                    ['id', 'amount', 'type', 'subtype', 'status', 'reference']
                ),
                'stk_result_code' => $stkCallback->result_code
            ]
        ]);
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback)
    {
        $payment = Payment::whereProvider(PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();
        if ($payment->status !== Status::PENDING->name) throw new \Error("Payment is not pending... - $payment->id");

        $payment->update(["status" => Status::COMPLETED->name]);

        Log::info('...[REP - MPESA]: Payment updated...', [$payment->id, $payment->status]);

        $data['payments'] = [Arr::only(
            $payment->toArray(),
            ['id', 'amount', 'type', 'subtype', 'status', 'reference']
        )];

        if ($stkCallback->request->reference === MpesaReference::PAY_VOUCHER) {
            // TODO: If you purchase for self using other MPESA, this fails!!!
            $destination = explode(' - ', $payment->description)[1];
            $accountId = SidoohAccounts::findByPhone($destination)['id'];

            [$voucher,] = VoucherRepository::credit($accountId, $payment->amount, Description::VOUCHER_PURCHASE);

            $data['credit_vouchers'] = [$voucher];
        }

        SidoohProducts::paymentCallback($data);
    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse)
    {
        try {
            $payment = Payment::whereProvider(PaymentSubtype::STK, $paymentResponse->request->id)->firstOrFail();
            if ($payment->status !== Status::PENDING->name) throw new \Error("Payment is not pending... - $payment->id");

            $payment->update(["status" => Status::COMPLETED->name]);

            Log::info('...[REPO]: B2C Payment updated...', $payment->toArray());

            SidoohSavings::paymentCallback($payment);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    public static function b2cPaymentFailed(MpesaBulkPaymentResponse $paymentResponse)
    {
        try {
            $payment = Payment::whereProvider(PaymentSubtype::STK, $paymentResponse->request->id)->firstOrFail();

            if ($payment->status !== Status::PENDING->name) throw new \Error("Payment is not pending... - $payment->id");

            $payment->update(["status" => Status::FAILED->name]);

            Log::info('...[REPO]: B2C Payment updated...', $payment->toArray());

            SidoohSavings::paymentCallback($payment);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
