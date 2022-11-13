<?php

namespace App\Rules;

use App\Models\Voucher;
use Exception;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SidoohVoucherIsForAccount implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        try {
            Voucher::whereId($value)->whereAccountId(request('account_id'))->firstOrFail();
        } catch (Exception) {
            $fail('The :attribute field must be a valid voucher.');
        }
    }
}
