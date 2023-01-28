<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Rules\SidoohFloatAccountExists;
use App\Rules\SidoohVoucherExists;
use Exception;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Validation\Rules\Enum;

class WithdrawalRequest extends PaymentRequest
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
     *
     * @throws \Exception
     */
    public function rules(): array
    {
        return parent::rules() + [
            'destination'         => ['bail', 'required', new Enum(PaymentMethod::class)],
            'destination_account' => ['required', $this->destinationAccountRule()],
        ];
    }

    /**
     * @throws Exception
     */
    public function sourceAccountRule(): InvokableRule|string
    {
        return match (PaymentMethod::tryFrom($this->input('source'))) {
            PaymentMethod::FLOAT => new SidoohFloatAccountExists,
            default              => abort(422, 'Unsupported source')
        };
    }

    /**
     * @throws Exception
     */
    public function destinationAccountRule(): InvokableRule|string
    {
        $countryCode = config('services.sidooh.country_code');

        return match (PaymentMethod::tryFrom($this->input('destination'))) {
            PaymentMethod::MPESA   => "phone:$countryCode",
            PaymentMethod::VOUCHER => new SidoohVoucherExists,
            default                => abort(422, 'Unsupported destination')
        /*throw new Exception('Unsupported destination', 422)*/
        };
    }
}
