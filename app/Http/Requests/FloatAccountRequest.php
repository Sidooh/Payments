<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloatAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'initiator' => ['in:ENTERPRISE,AGENT',],
//            'account_id'    => 'integer',
            'agent_id' => ['required_if:initiator,AGENT', 'exists:agents,id'],
            'enterprise_id' => ['required_if:initiator,ENTERPRISE', 'exists:enterprises,id'],
        ];
    }

}
