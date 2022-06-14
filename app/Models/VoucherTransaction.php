<?php

namespace App\Models;

use App\Enums\Description;
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

    protected $casts = [
        "description" => Description::class
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
