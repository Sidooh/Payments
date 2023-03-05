<?php

namespace App\Rules;

use App\Services\SidoohProducts;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class SidoohEnterpriseExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $account = SidoohProducts::findEnterprise($value);

            if (! isset($account['id'])) {
                $fail('The :attribute must be an existing enterprise.');
            }
        } catch (Exception) {
            $fail('The :attribute must be an existing enterprise.');
        }
    }
}
