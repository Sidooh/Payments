<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Rules\SidoohFloatAccountExists;

class DisburseVoucherTypeRequest extends PaymentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: check account is valid from jwt? if enterprise for instance.
        //  What about normal users?
        return true;
    }

    public function sourceAccountRule(): SidoohFloatAccountExists|string
    {
        return match (PaymentMethod::tryFrom($this->input('source'))) {
            PaymentMethod::FLOAT => new SidoohFloatAccountExists,
            default              => abort(422, 'Unsupported source')
        };
    }
}
