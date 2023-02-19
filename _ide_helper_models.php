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
 * @property int $balance
 * @property string $floatable_type
 * @property int $floatable_id
 * @property int|null $account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FloatAccountTransaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\FloatAccountFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereFloatableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccount whereFloatableType($value)
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
 * @property \App\Enums\TransactionType $type
 * @property int $amount
 * @property string $description
 * @property array|null $extra
 * @property int $float_account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FloatAccount $floatAccount
 * @property-read \App\Models\Payment|null $payment
 * @method static \Database\Factories\FloatAccountTransactionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FloatAccountTransaction whereExtra($value)
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
 * @property int $charge
 * @property \App\Enums\Status $status
 * @property \App\Enums\PaymentType $type
 * @property \App\Enums\PaymentSubtype $subtype
 * @property int $provider_id
 * @property string|null $description
 * @property string|null $reference
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $account_id
 * @property \App\Enums\PaymentType|null $destination_type
 * @property \App\Enums\PaymentSubtype|null $destination_subtype
 * @property int|null $destination_provider_id
 * @property array|null $destination_data
 * @property string|null $ipn
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $destinationProvider
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $provider
 * @method static \Database\Factories\PaymentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDestinationData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDestinationProvider(\App\Enums\PaymentSubtype $subtype, int $providerId)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDestinationProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDestinationSubtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProvider(\App\Enums\PaymentSubtype $subtype, int $providerId)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereReference($value)
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
 * @property int $balance
 * @property \App\Enums\Status $status
 * @property int $account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $voucher_type_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherTransaction[] $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\VoucherType|null $voucherType
 * @method static \Database\Factories\VoucherFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereVoucherTypeId($value)
 */
	class IdeHelperVoucher {}
}

namespace App\Models{
/**
 * App\Models\VoucherTransaction
 *
 * @property int $id
 * @property \App\Enums\TransactionType $type
 * @property int $amount
 * @property string $description
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

namespace App\Models{
/**
 * App\Models\VoucherType
 *
 * @property int $id
 * @property string $name
 * @property int $is_locked
 * @property int $limit_amount
 * @property string|null $expires_at
 * @property array|null $settings
 * @property int $account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Voucher[] $vouchers
 * @property-read int|null $vouchers_count
 * @method static \Database\Factories\VoucherTypeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType query()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereLimitAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherType whereUpdatedAt($value)
 */
	class IdeHelperVoucherType {}
}

