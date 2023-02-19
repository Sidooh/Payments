<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperVoucher
 */
class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'voucher_type_id',
        'status',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    protected $with = ['voucherType'];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function voucherType(): BelongsTo
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(VoucherTransaction::class);
    }
}
