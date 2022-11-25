<?php

namespace App\Http\Requests;

use App\Enums\Initiator;
use App\Rules\SidoohAccountExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: check account is valid from jwt? if enterprise for instance.
        //  What about normal users?
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'initiator'  => ['required', 'in:' . Initiator::ENTERPRISE->value],
            'account_id' => ['required', 'integer', new SidoohAccountExists],
            'name'       => ['required', 'string'],
        ];
    }
}
