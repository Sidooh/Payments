<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            "id"         => $this->id,
            "payable_id" => $this->payable_id,
            "amount"     => $this->amount,
            "status"     => $this->status,
            "type"       => $this->type,
            "subtype"    => $this->subtype,
        ];
    }
}
