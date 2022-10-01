<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
use App\Rules\SidoohAccountExists;
use App\Rules\SidoohEnterpriseExists;
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
            'initiator'    => ['required', 'in:'.Initiator::ENTERPRISE->value.','.Initiator::AGENT->value],
            'floatable_id' => [
                'required',
                $this->initiator === Initiator::AGENT->value ? new SidoohAccountExists : new SidoohEnterpriseExists,
                Rule::unique('float_accounts')->where('floatable_type', $this->initiator),
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
