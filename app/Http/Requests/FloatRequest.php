<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
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
            'initiator' => ['required', Rule::in([Initiator::ENTERPRISE->value, Initiator::MERCHANT->value])],
            'amount'    => ['required_unless:initiator,null', 'numeric'],
        ];
    }
}
