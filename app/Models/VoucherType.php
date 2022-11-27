<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperVoucherType
 */
class VoucherType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'account_id'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
