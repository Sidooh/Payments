<?php

namespace App\Rules;

use App\Enums\Initiator;
use App\Models\FloatAccount;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SidoohMerchantFloatAccountExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            FloatAccount::whereId($value)->whereFloatableType(Initiator::MERCHANT->value)->firstOrFail();
        } catch (Exception) {
            $fail('The :attribute must be a valid merchant float account.');
        }
    }
}
