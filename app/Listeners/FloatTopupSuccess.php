<?php

namespace App\Listeners;

use App\Events\FloatTopupEvent;
use App\Repositories\EventRepositories\SidoohEventRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class FloatTopupSuccess
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
     * @param  FloatTopupEvent  $event
     * @return void
     *
     * @throws Throwable
     */
    public function handle(FloatTopupEvent $event): void
    {
        Log::info('...[EVENT]: Float topped up...');

        try {
            SidoohEventRepository::floatTopUp($event->transaction);
        } catch (Exception $e) {
            Log::critical($e);
        }
    }
}
