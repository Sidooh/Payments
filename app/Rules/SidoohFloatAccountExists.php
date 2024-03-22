<?php

namespace App\Rules;

use App\Models\FloatAccount;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SidoohFloatAccountExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            FloatAccount::whereId($value)->firstOrFail();
        } catch (Exception) {
            $fail('The :attribute must be a valid float account.');
        }
    }
}
