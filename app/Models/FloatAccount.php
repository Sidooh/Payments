<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperFloatAccount
 */
class FloatAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'floatable_id',
        'floatable_type',
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    /**
     * Get the parent floatable model (agent or enterprise).
     */
    public function floatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function floatAccountTransactions(): HasMany
    {
        return $this->hasMany(FloatAccountTransaction::class);
    }
}
