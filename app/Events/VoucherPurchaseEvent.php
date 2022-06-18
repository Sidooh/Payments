<?php


namespace App\Events;


use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherPurchaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Create a new event instance.
     *
     * @param Voucher $voucher
     * @param int     $transactionId
     */
    public function __construct(public Voucher $voucher, public $amount)
    {
        //TODO: Is this event in use? Should it be?
    }
}
