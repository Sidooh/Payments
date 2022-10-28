<?php

namespace App\Models;

use App\Enums\PaymentSubtype;
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
        'amount',
        'status',
        'type',
        'subtype',
        'provider_id',
        'reference',
        'description',
    ];

    public function provider(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subtype', 'provider_id');
    }

    public function destinationProvider(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'destination_subtype', 'destination_provider_id');
    }

    /**
     * Scope a query to fetch specific provider.
     *
     * @param  Builder  $query
     * @param  PaymentSubtype  $subtype
     * @param  int  $providerId
     * @return Builder
     */
    public function scopeWhereProvider(Builder $query, PaymentSubtype $subtype, int $providerId): Builder
    {
        return $query->whereSubtype($subtype->name)->whereProviderId($providerId);
    }

    /**
     * Scope a query to fetch specific provider.
     *
     * @param  Builder  $query
     * @param  PaymentSubtype  $subtype
     * @param  int  $providerId
     * @return Builder
     */
    public function scopeWhereDestinationProvider(Builder $query, PaymentSubtype $subtype, int $providerId): Builder
    {
        return $query->whereDestinationSubtype($subtype->name)->whereDestinationProviderId($providerId);
    }
}
