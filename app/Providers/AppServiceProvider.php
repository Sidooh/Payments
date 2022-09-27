<?php

namespace App\Providers;

use App\Models\FloatAccountTransaction;
use App\Models\VoucherTransaction;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaStkRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Model::preventLazyLoading(! app()->isProduction());

        Relation::enforceMorphMap([
            'STK' => MpesaStkRequest::class,
            'VOUCHER' => VoucherTransaction::class,
            'B2C' => MpesaBulkPaymentRequest::class,

            'FLOAT' => FloatAccountTransaction::class,
        ]);
    }
}
