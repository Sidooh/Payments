<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Rules\SidoohMerchantFloatAccountExists;
use Exception;
use Illuminate\Validation\Rules\Enum;

class MerchantFloatSendRequest extends PaymentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     *
     * @throws \Exception
     */
    public function rules(): array
    {
        return parent::rules() + [
            'destination'         => ['bail', 'required', new Enum(PaymentMethod::class)],
            'destination_account' => ['required', 'numeric', 'different:source_account', $this->destinationAccountRule()],
        ];
    }

    /**
     * @throws Exception
     */
    public function sourceAccountRule(): SidoohMerchantFloatAccountExists
    {
        $countryCode = config('services.sidooh.country_code');

        return match (PaymentMethod::tryFrom($this->input('source'))) {
            PaymentMethod::FLOAT => new SidoohMerchantFloatAccountExists,
            default              => abort(422, 'Unsupported source')
        };
    }

    /**
     * @throws Exception
     */
    public function destinationAccountRule(): SidoohMerchantFloatAccountExists
    {
        return match (PaymentMethod::tryFrom($this->input('destination'))) {
            PaymentMethod::FLOAT => new SidoohMerchantFloatAccountExists,
            default                => abort(422, 'Unsupported destination')
        };
    }
}
