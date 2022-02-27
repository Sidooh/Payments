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
        'accountable_id',
        'accountable_type',
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    /**
     * Get the parent accountable model (agent or enterprise).
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function floatAccountTransaction():HasMany {
        return $this->hasMany(FloatAccountTransaction::class);
    }
}
