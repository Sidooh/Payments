<?php

namespace App\Http\Requests;

use App\Rules\SidoohAccountExists;
use App\Rules\SidoohEnterpriseExists;
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
            'initiator'     => 'required|in:ENTERPRISE,AGENT',
            'account_id'    => ['required_if:initiator,AGENT', new SidoohAccountExists],
            'enterprise_id' => ['required_if:initiator,ENTERPRISE', new SidoohEnterpriseExists],
        ];
    }

}
