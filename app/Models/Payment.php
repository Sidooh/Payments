<?php

namespace App\Models;

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
        'provider_type',
        'provider_id',
    ];

    /**
     * Get the parent payable model (transaction or ...).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
