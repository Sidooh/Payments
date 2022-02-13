<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperVoucherTransaction
 */
class VoucherTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'amount',
        'description',
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
