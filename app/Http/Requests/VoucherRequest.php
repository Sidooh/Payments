<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class VoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[Pure]
    #[ArrayShape([
        'initiator'     => "array",
        'disburse_type' => "string[]",
        'account_id'    => "string",
        'enterprise_id' => "string[]",
        'amount'        => "string[]",
        'accounts'      => "string[]"
    ])]
    public function rules(): array
    {
        return [
            'disburse_type' => ['in:LUNCH,GENERAL',],
            'account_id'    => 'integer',
            'enterprise_id' => ['required_if:initiator,ENTERPRISE', 'exists:enterprises,id'],
            'amount'        => ['required_unless:initiator,null', 'numeric'],
            'accounts'      => ['array'],
        ];
    }

    #[ArrayShape(['disburse_type.in' => "string"])]
    public function messages(): array
    {
        return [
            'disburse_type.in' => 'invalid :attribute. allowed values are: [LUNCH, GENERAL]'
        ];
    }
}
