<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloatAccountTopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'float_account' => ['required', 'exists:float_accounts,id'],
        ];
    }
}
