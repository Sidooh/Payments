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
