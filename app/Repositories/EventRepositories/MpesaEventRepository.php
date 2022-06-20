<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\EventType;
use App\Enums\MpesaReference;
use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Models\Payment;
use App\Repositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use App\Services\SidoohProducts;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaEventRepository extends EventRepository
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function stkPaymentFailed($stkCallback)
    {
        // TODO: Make into a transaction/try catch?
        $p = Payment::whereProviderId($stkCallback->request->id)->whereSubtype(PaymentSubtype::STK->name)->firstOrFail();

        if($p->status == Status::FAILED->name) return;

        $p->status = Status::FAILED->name;
        $p->save();

//        TODO: Refactor to pass data like success payment callback
        SidoohProducts::paymentCallback(["payments" => $p->toArray()]);

        //  TODO: Can we inform the user of the actual issue?
        $message = match ($stkCallback->ResultCode) {
            1 => "You have insufficient Mpesa Balance for this transaction. Kindly top up your Mpesa and try again.",
            default => "Sorry! We failed to complete your transaction. No amount was deducted from your account. We apologize for the inconvenience. Please try again.",
        };
//        TODO: Should this be sent to the transaction initiator in the case of using other mpesa no.?
        SidoohNotify::notify([$stkCallback->request->phone], $message, EventType::PAYMENT_FAILURE);
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback)
    {
        $payment = Payment::whereProviderId($stkCallback->request->id)
            ->whereSubtype(PaymentSubtype::STK->name)
            ->firstOrFail();
        $payment->update(["status" => Status::COMPLETED->name]);

        Log::info('...[REPO]: Payment updated...', [$payment]);

        $purchaseData = match ($stkCallback->request->reference) {
            MpesaReference::AIRTIME, MpesaReference::PAY_VOUCHER => [
                'phone' => $payment->details,
            ],
            MpesaReference::PAY_UTILITY => [
                'account'  => $payment->details,
                'provider' => explode(" ", $stkCallback->request->description)[0],
            ],
            default => []
        };

        $data = array_merge($purchaseData, ["payments" => [$payment->toArray()]]);

        if($stkCallback->request->reference === MpesaReference::PAY_VOUCHER) {
            // TODO: If you purchase for self using other MPESA, this fails!!!
            $accountId = SidoohAccounts::findByPhone($payment->details)['id'];

            $voucher = VoucherRepository::credit($accountId, $payment->amount, Description::VOUCHER_PURCHASE, true);

            $data['vouchers'] = [$voucher];
        }

        SidoohProducts::paymentCallback($data);
    }
}
