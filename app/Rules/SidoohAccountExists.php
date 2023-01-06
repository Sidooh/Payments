<?php

namespace App\Rules;

use App\Services\SidoohAccounts;
use Exception;
use Illuminate\Contracts\Validation\InvokableRule;

class SidoohAccountExists implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
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
