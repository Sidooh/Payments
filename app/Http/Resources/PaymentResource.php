<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $base = [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'status'      => $this->status,
            'type'        => $this->type,
            'subtype'     => $this->subtype,
            'description' => $this->description,
            'reference'   => $this->reference,
            'destination' => $this->destination_data,
        ];

        if (isset($this->error_code)) {
            $base['error_code'] = $this->error_code;
            $base['error_message'] = $this->error_message;
        }

        return $base;
    }
}
