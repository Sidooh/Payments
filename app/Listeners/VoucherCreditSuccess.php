<?php

namespace App\Listeners;

use App\Events\VoucherCreditEvent;
use App\Repositories\EventRepositories\SidoohEventRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class VoucherCreditSuccess
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
        Log::info('...[EVENT]: Voucher Credited...');

        // TODO: Handle async?
        try {
            SidoohEventRepository::voucherCredited($event->transaction);
        } catch (Exception $e) {
            Log::critical($e);
        }
    }
}
