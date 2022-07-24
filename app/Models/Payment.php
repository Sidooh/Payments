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
        'provider_id',
        'reference',
        'description'
    ];

    public function providable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subtype', 'provider_id');
    }
}
