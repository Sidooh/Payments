<?php

namespace App\Models;

use App\Enums\VoucherType;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperVoucher
 */
class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type'
    ];

    public function voucherTopUpAmount(): Attribute
    {
        return new Attribute(get: function($value, $attributes) {

            $disburseType = match (VoucherType::from($this->type)) {
                VoucherType::ENTERPRISE_LUNCH => 'lunch',
                VoucherType::ENTERPRISE_GENERAL => 'general',
                default => throw new Exception('Unexpected match value'),
            };

            ['max' => $max] = collect($this->enterprise->settings)->firstWhere('type', $disburseType);

            return $max - $attributes['balance'];
        });
    }

    /**
     * ---------------------------------------- Relationships ----------------------------------------
     */
    public function voucherTransaction(): HasMany
    {
        return $this->hasMany(VoucherTransaction::class);
    }
}
