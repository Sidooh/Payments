<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Rules\SidoohAccountExists;
use App\Rules\SidoohFloatAccountExists;
use App\Rules\SidoohVoucherExists;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PaymentRequest extends FormRequest
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
            'account_id'  => ['required', 'integer', new SidoohAccountExists],
            'amount'      => ['required', 'integer'],
            'description' => ['required', 'string'],
            'reference'   => ['nullable', 'string'],

            'source'              => ['required', new Enum(PaymentMethod::class)],
            'source_account'      => ['required', $this->sourceAccountRule()],

            'ipn' => ['nullable', 'url'],
        ];
    }

    function sourceAccountRule(): InvokableRule|string
    {
        $countryCode = config('services.sidooh.country_code');

        return match (PaymentMethod::tryFrom($this->input('source'))) {
            PaymentMethod::MPESA => "phone:$countryCode",
            PaymentMethod::VOUCHER => new SidoohVoucherExists,
            PaymentMethod::FLOAT => new SidoohFloatAccountExists,
            default => abort(422, 'Unsupported source')
        };
    }
}
