<?php

namespace App\Repositories;

use App\Models\FloatAccount;

class FloatAccountRepository
{
    public function store(int $accountId, string $accountType): FloatAccount
    {
        return FloatAccount::create([
            'accountable_id' => $accountId,
            'accountable_type' => $accountType
        ]);
    }
}
