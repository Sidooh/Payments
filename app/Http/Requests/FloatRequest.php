<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
use App\Rules\SidoohAccountExists;
use App\Rules\SidoohEnterpriseExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FloatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'initiator'     => ["required", Rule::in([Initiator::ENTERPRISE->value, Initiator::AGENT->value])],
            'account_id'    => ["required_if:initiator," . Initiator::AGENT->value, "integer", new SidoohAccountExists],
            'enterprise_id' => [
                "required_if:initiator," . Initiator::ENTERPRISE->value,
                "integer",
                new SidoohEnterpriseExists
            ],
            'amount'        => ["required_unless:initiator,null", "numeric"]
        ];
    }
}
