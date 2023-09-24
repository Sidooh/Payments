<?php

namespace App\Providers;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Models\FloatAccountTransaction;
use App\Models\VoucherTransaction;
use DrH\Buni\Models\BuniStkRequest;
use DrH\Mpesa\Entities\MpesaB2bRequest;
use DrH\Mpesa\Entities\MpesaBulkPaymentRequest;
use DrH\Mpesa\Entities\MpesaC2bCallback;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\TendePay\Models\TendePayRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
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
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);

                Log::warning("Attempted to lazy load [$relation] on model [$class].");
            });
        }

        Relation::enforceMorphMap([
            PaymentType::MPESA->name . PaymentSubtype::STK->name => MpesaStkRequest::class,
            PaymentType::MPESA->name . PaymentSubtype::B2C->name => MpesaBulkPaymentRequest::class,
            PaymentType::MPESA->name . PaymentSubtype::C2B->name => MpesaC2bCallback::class,
            PaymentType::MPESA->name . PaymentSubtype::B2B->name => MpesaB2bRequest::class,

            PaymentType::TENDE->name . PaymentSubtype::B2B->name => TendePayRequest::class,
            PaymentType::BUNI->name . PaymentSubtype::STK->name  => BuniStkRequest::class,

            PaymentType::SIDOOH->name . PaymentSubtype::VOUCHER->name => VoucherTransaction::class,
            PaymentType::SIDOOH->name . PaymentSubtype::FLOAT->name   => FloatAccountTransaction::class,
        ]);
    }
}
