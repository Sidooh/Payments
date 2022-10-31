<?php

namespace App\Http\Requests;

use App\Enums\MerchantType;
use Illuminate\Validation\Rules\Enum;

class MerchantPaymentRequest extends PaymentRequest
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
                'merchant_type'         => ['required', new Enum(MerchantType::class)],
                ...$this->destinationAccountRule()
            ];
    }

    function destinationAccountRule(): array
    {
        return match (MerchantType::tryFrom($this->input('merchant_type'))) {
            MerchantType::MPESA_PAY_BILL => [
                'paybill_number' => ['required', 'integer'],
                'account_number' => ['required', 'string'],
            ],
            MerchantType::MPESA_BUY_GOODS => [
                'till_number'    => ['required', 'integer'],
                'account_number' => ['nullable', 'string'],
            ],
            default => []
        };
    }
}
