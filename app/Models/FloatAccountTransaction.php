<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin IdeHelperFloatAccountTransaction
 */
class FloatAccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'balance',
        'type',
        'description',
        'extra',
    ];

    protected $casts = [
        'amount' => 'int',
        'balance' => 'int',
        'type'   => TransactionType::class,
        'extra'  => 'array',
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function floatAccount(): BelongsTo
    {
        return $this->belongsTo(FloatAccount::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'provider', 'subtype');
    }
}
