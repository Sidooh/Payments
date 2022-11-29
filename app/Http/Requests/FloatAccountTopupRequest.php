<?php

namespace App\Http\Requests;

use App\Rules\SidoohFloatAccountExists;
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
            'float_account' => ['required', new SidoohFloatAccountExists],
        ];
    }
}
