<?php

namespace App\Listeners;

use App\Events\FloatTopUpEvent;
use App\Repositories\EventRepositories\SidoohEventRepository;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class FloatTopUpSuccess implements ShouldQueue
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
     * @throws Throwable
     */
    public function handle(FloatTopUpEvent $event): void
    {
        Log::info('...[EVENT]: Float topped up...');

        try {
            SidoohEventRepository::floatTopUp($event->transaction);
        } catch (Exception $e) {
            Log::critical($e);
        }
    }
}
