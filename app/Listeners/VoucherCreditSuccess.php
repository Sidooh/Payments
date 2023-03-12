<?php

namespace App\Listeners;

use App\Events\VoucherCreditEvent;
use App\Repositories\EventRepositories\SidoohEventRepository;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class VoucherCreditSuccess implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     *
     * @throws Throwable
     */
    public function handle(VoucherCreditEvent $event): void
    {
        Log::info('...[EVENT]: Voucher credited...');

        // TODO: Handle async?
        try {
            SidoohEventRepository::voucherCredited($event->transaction);
        } catch (Exception $e) {
            Log::critical($e);
        }
    }
}
