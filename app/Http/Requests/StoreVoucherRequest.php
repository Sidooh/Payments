<?php

namespace App\Http\Requests;

use App\Rules\SidoohAccountExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
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
            'account_id'         => ['required', 'integer', new SidoohAccountExists],
            'voucher_type_id'    => ['required', 'integer', 'exists:voucher_types,id'],
        ];
    }
}
