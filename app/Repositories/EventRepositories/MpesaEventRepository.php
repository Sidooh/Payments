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
        $payment = Payment::whereProvidableId($stkCallback->request->id)
            ->whereSubtype(PaymentSubtype::STK->name)
            ->firstOrFail();

        if($payment->status == Status::FAILED->name) return;

        $payment->status = Status::FAILED->name;
        $payment->save();

        SidoohProducts::paymentCallback(["payments" => [[...$payment->toArray(), 'stk_result_code' => $stkCallback->result_code]]]);
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback)
    {
        $payment = Payment::whereProvidableId($stkCallback->request->id)
            ->whereSubtype(PaymentSubtype::STK->name)
            ->firstOrFail();
        $payment->update(["status" => Status::COMPLETED->name]);

        Log::info('...[REPO]: Payment updated...', [$payment]);

        $data['payments'] = [$payment->toArray()];

        if($stkCallback->request->reference === MpesaReference::PAY_VOUCHER) {
            // TODO: If you purchase for self using other MPESA, this fails!!!
            $accountId = SidoohAccounts::findByPhone($payment->details)['id'];

            $voucher = VoucherRepository::credit($accountId, $payment->amount, Description::VOUCHER_PURCHASE, true);

            $data['vouchers'] = [$voucher];
        }

        SidoohProducts::paymentCallback($data);
    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse)
    {
        try {
            $payment = Payment::whereProvidableId($paymentResponse->request->id)
                ->whereSubtype(PaymentSubtype::B2C->name)
                ->firstOrFail();
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
            $payment = Payment::whereProvidableId($paymentResponse->request->id)
                ->whereSubtype(PaymentSubtype::B2C->name)
                ->firstOrFail();
            $payment->update(["status" => Status::FAILED->name]);

            Log::info('...[REPO]: B2C Payment updated...', $payment->toArray());

            SidoohSavings::paymentCallback($payment);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
