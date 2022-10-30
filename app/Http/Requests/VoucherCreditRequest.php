<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Rules\SidoohVoucherIsForAccount;
use Illuminate\Contracts\Validation\InvokableRule;

class VoucherCreditRequest extends PaymentRequest
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
        return parent::rules() + [
                'voucher' => ['required', 'different:source_account'],
            ];
    }

    function sourceAccountRule(): InvokableRule|string
    {
        return match (PaymentMethod::tryFrom($this->input('source'))) {
            PaymentMethod::VOUCHER => new SidoohVoucherIsForAccount,
            default => parent::sourceAccountRule()
        };
    }
}
