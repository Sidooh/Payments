<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperFloatAccount
 */
class FloatAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'floatable_id',
        'floatable_type',
        'account_id',
    ];

    protected $casts = [
        'balance' => 'int',
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FloatAccountTransaction::class)->latest();
    }
}
