<?php

namespace App\Rules;

use App\Services\SidoohAccounts;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class SidoohAccountExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $account = SidoohAccounts::find($value);

            if (! isset($account['id'])) {
                $fail('The :attribute must be a valid Sidooh account.');
            }
        } catch (Exception) {
            $fail('The :attribute must be a valid Sidooh account.');
        }
    }
}
