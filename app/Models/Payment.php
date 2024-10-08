<?php

namespace App\Models;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperPayment
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'amount',
        'charge',
        'status',
        'type',
        'subtype',
        'provider_id',
        'reference',
        'description',
        'ipn',
        'destination_type',
        'destination_subtype',
        'destination_provider_id',
        'destination_data',
    ];

    protected $casts = [
        'destination_data'    => 'array',
        'type'                => PaymentType::class,
        'status'              => Status::class,
        'subtype'             => PaymentSubtype::class,
        'destination_type'    => PaymentType::class,
        'destination_subtype' => PaymentSubtype::class,
    ];

    public function provider(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'provider_text', 'provider_id');
    }

    public function destinationProvider(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'destination_provider_text', 'destination_provider_id');
    }

    /**
     * Scope a query to fetch specific provider.
     *
     * @param  Builder  $query
     * @param  PaymentSubtype  $subtype
     * @param  int  $providerId
     * @return Builder
     */
    public function scopeWhereProvider(Builder $query, PaymentType $type, PaymentSubtype $subtype, int $providerId): Builder
    {
        return $query->whereType($type->name)->whereSubtype($subtype->name)->whereProviderId($providerId);
    }

    /**
     * Scope a query to fetch specific provider.
     *
     * @param  Builder  $query
     * @param  PaymentSubtype  $subtype
     * @param  int  $providerId
     * @return Builder
     */
    public function scopeWhereDestinationProvider(Builder $query, PaymentType $type, PaymentSubtype $subtype, int $providerId): Builder
    {
        return $query->whereDestinationType($type->name)->whereDestinationSubtype($subtype->name)->whereDestinationProviderId($providerId);
    }
}
