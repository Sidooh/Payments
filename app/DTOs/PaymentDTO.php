<?php

namespace App\DTOs;

use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Services\SidoohAccounts;

class PaymentDTO
{
    public function __construct(
        protected int          $accountId,
        public int             $amount,
        public PaymentType     $type,
        public PaymentSubtype  $subtype,
        public string          $description,
        public string          $reference,
        public ?PaymentType    $destinationType = null,
        public ?PaymentSubtype $destinationSubtype = null,
        public ?array           $destinationData = null,
    )
    {
        $this->validate();
    }

    function validate(): void
    {
        SidoohAccounts::find($this->accountId);
    }
}
