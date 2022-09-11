<?php

namespace App\Rules;

use App\Services\SidoohProducts;
use Exception;
use Illuminate\Contracts\Validation\InvokableRule;

class SidoohEnterpriseExists implements InvokableRule
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
            $account = SidoohProducts::findEnterprise($value);

            if (!isset($account['id'])) {
                $fail('The :attribute must be an existing enterprise.');
            }
        } catch (Exception) {
            $fail('The :attribute must be an existing enterprise.');
        }
    }
}
