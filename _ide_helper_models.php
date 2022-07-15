<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\FloatAccount
 *
 * @property int $id
 * @property string $balance
 * @property string $accountable_type
 * @property int $accountable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $accountable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FloatAccountTransaction[] $floatAccountTransaction
 * @property-read int|null $float_account_transaction_count
 * @method static \Database\Factories\FloatAccountFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereAccountableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereAccountableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereUpdatedAt($value)
 */
	class IdeHelperFloatAccount {}
}

namespace App\Models{
/**
 * App\Models\FloatAccountTransaction
 *
 * @property int $id
 * @property string $type
 * @property string $amount
 * @property string $description
 * @property int $float_account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FloatAccount $floatAccount
 * @method static \Database\Factories\FloatAccountTransactionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereFloatAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereUpdatedAt($value)
 */
	class IdeHelperFloatAccountTransaction {}
}

namespace App\Models{
/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string $amount
 * @property string $details
 * @property string $status
 * @property string $type
 * @property string $subtype
 * @property string $providable_type
 * @property int $providable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $provider
 * @method static \Database\Factories\PaymentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProvidableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProvidableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSubtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 */
	class IdeHelperPayment {}
}

namespace App\Models{
/**
 * App\Models\Voucher
 *
 * @property int $id
 * @property string $type
 * @property int $balance
 * @property int $account_id
 * @property int|null $enterprise_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherTransaction[] $voucherTransactions
 * @property-read int|null $voucher_transactions_count
 * @method static \Database\Factories\VoucherFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereEnterpriseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereUpdatedAt($value)
 */
	class IdeHelperVoucher {}
}

namespace App\Models{
/**
 * App\Models\VoucherTransaction
 *
 * @property int $id
 * @property string $type
 * @property int $amount
 * @property \App\Enums\Description $description
 * @property int $voucher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\Voucher $voucher
 * @method static \Database\Factories\VoucherTransactionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherTransaction whereVoucherId($value)
 */
	class IdeHelperVoucherTransaction {}
}

