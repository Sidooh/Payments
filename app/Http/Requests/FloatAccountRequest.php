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
            'initiator'  => ['required', 'in:' . Initiator::ENTERPRISE->value . ',' . Initiator::AGENT->value],
            'account_id' => ['required', 'integer', new SidoohAccountExists],
            'reference'  => [
                'required',
                Rule::unique('float_accounts', 'floatable_id')
                    ->where('floatable_type', $this->initiator)
                    ->where('account_id', $this->account_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Float account already exists.',
        ];
    }
}
