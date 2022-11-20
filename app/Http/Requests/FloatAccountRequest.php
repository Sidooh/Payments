<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
use App\Rules\SidoohAccountExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FloatAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'initiator'  => ['required', 'in:'.Initiator::ENTERPRISE->value.','.Initiator::AGENT->value],
            'account_id' => [
                'required',
                new SidoohAccountExists,
                Rule::unique('float_accounts'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'floatable_id.unique' => 'Float account already exists.',
        ];
    }
}
