<?php

namespace App\Providers;

use App\Helpers\LocalCarbon;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('localcarbon', fn () => new LocalCarbon);
    }
}
