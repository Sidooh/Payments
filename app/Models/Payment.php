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
        'status',
        'type',
        'subtype',
        'reference',
        'description'
    ];

    public function providable(): MorphTo
    {
        return $this->morphTo();
    }
}
