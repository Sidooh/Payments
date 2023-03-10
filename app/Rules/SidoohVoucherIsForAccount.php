<?php

namespace App\Rules;

use App\Models\Voucher;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SidoohVoucherIsForAccount implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            Voucher::whereId($value)->whereAccountId(request('account_id'))->firstOrFail();
        } catch (Exception) {
            $fail('The :attribute field must be a valid voucher.');
        }
    }
}
