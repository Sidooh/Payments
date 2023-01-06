<?php

namespace App\Rules;

use App\Models\FloatAccount;
use Exception;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SidoohFloatAccountExists implements InvokableRule
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
            FloatAccount::whereId($value)/*->whereFloatableId(request('account_id'))*/->firstOrFail();
        } catch (Exception) {
            $fail('The :attribute must be a valid float account.');
        }
    }
}
