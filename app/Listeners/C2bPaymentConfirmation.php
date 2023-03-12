<?php

namespace App\Listeners;

use App\Repositories\EventRepositories\MpesaEventRepository;
use DrH\Mpesa\Entities\MpesaStkCallback;
use DrH\Mpesa\Events\C2bConfirmationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class C2bPaymentConfirmation implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @throws \Exception
     */
    public function handle(C2bConfirmationEvent $event): void
    {
        $c2b = $event->c2bCallback;
        //Try to check if this was from STK
        $request = MpesaStkCallback::whereMpesaReceiptNumber($c2b->trans_id);

        Log::info('C2B Listener: ', [
            'c2b'           => $c2b,
            'api_json_data' => $event->apiJsonData,
            'stk_req'       => $request->get(),
        ]);

        if ($request->doesntExist()) {
            MpesaEventRepository::c2bPaymentConfirmed($c2b);
        }
    }
}

//  C2B Payment Callback
/*{
    invoice_number: null
    trans_time: "20230311102523"
    bill_ref_number: null
    msisdn: "254110039317"
    middle_name: "otunga"
    transaction_type: "Customer Merchant Payment"
    org_account_balance: "6159.95"
    first_name: "Michael"
    created_at: "2023-03-11T07:25:25.000000Z"
    third_party_trans_id: null
    updated_at: "2023-03-11T07:25:25.000000Z"
    business_short_code: "7400550"
    trans_amount: "20.00"
    trans_id: "RCB3IXGWAJ"
    last_name: "nabangi"
    id: 83
}*/
