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
        'providable_type',
        'providable_id',
        'amount',
        'details',
        'status',
        'type',
        'subtype',
    ];

    public function provider(): MorphTo
    {
        return $this->morphTo();
    }
}
