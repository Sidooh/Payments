<?php

namespace App\Http\Resources;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        $base = [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'charge'      => $this->charge,
            'status'      => $this->status,
            'type'        => $this->type === PaymentType::BUNI ? PaymentType::MPESA : $this->type,
            'subtype'     => $this->subtype,
            'description' => $this->description,
            'reference'   => $this->reference,
            'destination' => $this->destination_data,
        ];

        if (isset($this->error_code)) {
            $base['error_code'] = $this->error_code;
            $base['error_message'] = $this->error_message;
        }

        if (isset($this->mpesa_code)) {
            $base['mpesa_code'] = $this->mpesa_code;
            $base['mpesa_merchant'] = $this->mpesa_merchant;
            $base['mpesa_account'] = $this->mpesa_account;
        }

        if ($this->destination_subtype === PaymentSubtype::B2B && isset($this->destinationProvider->response) && $this->status == Status::COMPLETED) {
            $base['store'] = $this->destinationProvider->response->credit_party_public_name;
        }

        return $base;
    }
}
