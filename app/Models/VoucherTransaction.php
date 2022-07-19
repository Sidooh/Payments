<?php

namespace App\Models;

use App\Enums\Description;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        'type' => TransactionType::class,
        "description" => Description::class
    ];

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function payment(): MorphOne {
        return $this->morphOne(Payment::class, 'providable');
    }
}
