<?php

namespace App\Repositories;

use App\Enums\Initiator;
use App\Models\FloatAccount;
use Exception;

class FloatAccountRepository
{
    /**
     * @throws \Exception
     */
    public function store(Initiator $initiator, ?int $accountId, ?int $enterpriseId): FloatAccount
    {
        return FloatAccount::create([
            'floatable_id'   => match ($initiator) {
                Initiator::ENTERPRISE => $enterpriseId,
                Initiator::AGENT => $accountId,
                default => throw new Exception('Unexpected initiator value.'),
            },
            'floatable_type' => $initiator
        ]);
    }
}
