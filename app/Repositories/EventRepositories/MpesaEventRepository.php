<?php

namespace App\Repositories\EventRepositories;

use App\Enums\Description;
use App\Enums\EventType;
use App\Enums\MpesaReference;
use App\Enums\Status;
use App\Models\Payment;
use App\Repositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use App\Services\SidoohProducts;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Throwable;

class MpesaEventRepository extends EventRepository
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function stkPaymentFailed($stkCallback)
    {
        // TODO: Make into a transaction/try catch?
        $p = Payment::whereProviderId($stkCallback->request->id)->whereSubtype('STK')->firstOrFail();

        if($p->status == Status::FAILED->name) return;

        $p->status = Status::FAILED->name;
        $p->save();

//        TODO: Refactor to pass data like success payment callback
        SidoohProducts::paymentCallback(["payments" => $p->get()->toArray()]);

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
        $otherPhone = explode(" - ", $stkCallback->request->description);

        $payments = Payment::whereProviderId($stkCallback->request->id)->whereSubtype('STK');
        $payments->update(["status" => Status::COMPLETED->name]);

        $purchaseData = match ($stkCallback->request->reference) {
            MpesaReference::AIRTIME, MpesaReference::PAY_VOUCHER => [
                'phone' => count($otherPhone) > 1 ? $otherPhone[1]
                    : $stkCallback->PhoneNumber ?? $stkCallback->request->phone,
            ],
            MpesaReference::PAY_UTILITY => [
                'account'  => $otherPhone[1],
                'provider' => explode(" ", $stkCallback->request->description)[0],
            ],
            default => []
        };

        $data = array_merge($purchaseData, ["payments" => $payments->get()->toArray()]);

        if($stkCallback->request->reference === MpesaReference::PAY_VOUCHER) {
            $accountId = SidoohAccounts::findByPhone($purchaseData['phone'])['id'];

            VoucherRepository::credit($accountId, $stkCallback->amount, Description::VOUCHER_PURCHASE, true);
        } else {
            SidoohProducts::paymentCallback($data);
        }
    }
}
