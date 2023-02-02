<?php

namespace App\Providers;

use App\Models\FloatAccountTransaction;
use App\Models\VoucherTransaction;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaC2bCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\TendePay\Models\TendePayRequest;
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

        // Everything strict, all the time.
        Model::shouldBeStrict();

        // In production, merely log lazy loading violations.
        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function($model, $relation) {
                $class = get_class($model);

                info("Attempted to lazy load [$relation] on model [$class].");
            });
        }

        Relation::enforceMorphMap([
            'STK'     => MpesaStkRequest::class,
            'VOUCHER' => VoucherTransaction::class,
            'B2C'     => MpesaBulkPaymentRequest::class,
            'C2B'     => MpesaC2bCallback::class,
            'B2B'     => TendePayRequest::class,

            'FLOAT'   => FloatAccountTransaction::class,
        ]);
    }
}
