<?php

use App\Services\SidoohAccounts;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

if (! function_exists('base_64_url_encode')) {
    function base_64_url_encode($text): array|string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
}

if (! function_exists('withRelation')) {
    /**
     * @throws Exception
     */
    function withRelation($relation, $parentRecords, $parentKey, $childKey)
    {
        $childRecords = collect(match ($relation) {
            'account' => SidoohAccounts::getAll(),
            default   => throw new BadRequestException('Invalid relation!')
        });

        return $parentRecords->transform(function($record) use ($parentKey, $relation, $childKey, $childRecords) {
            $record[$relation] = $childRecords->firstWhere($childKey, $record[$parentKey]);

            return $record;
        });
    }
}

if (! function_exists('withdrawal_charge')) {
    /**
     * @throws Exception
     */
    function withdrawal_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.withdrawal');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] >= $amount && $ch['min'] <= $amount);

        if (!$charge) {
            throw new Exception('Withdrawal charge not found!');
        }

        return $charge['charge'];
    }
}

if (! function_exists('pay_bill_charge')) {
    /**
     * @throws Exception
     */
    function pay_bill_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.pay_bill');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] >= $amount && $ch['min'] <= $amount);

        if (!$charge) {
            throw new Exception('PayBill charge not found!');
        }

        return $charge['charge'];
    }
}

if (! function_exists('buy_goods_charge')) {
    /**
     * @throws Exception
     */
    function buy_goods_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.buy_goods');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] >= $amount && $ch['min'] <= $amount);

        if (!$charge) {
            throw new Exception('Buy Goods charge not found!');
        }

        return $charge['charge'];
    }
}

if (! function_exists('is_blacklisted_merchant')) {
    function is_blacklisted_merchant(int $code): bool
    {
        $blacklist = config('services.sidooh.merchants.blacklist') ?? [];

        return in_array($code, $blacklist);
    }
}

if (! function_exists('mpesa_float_charge')) {
    /**
     * @throws Exception
     */
    function mpesa_float_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.mpesa_float');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] >= $amount && $ch['min'] <= $amount);

        if (!$charge) {
            throw new Exception('Mpesa float charge not found!');
        }

        return $charge['charge'];
    }
}

if (! function_exists('mpesa_withdrawal_charge')) {
    /**
     * @throws Exception
     */
    function mpesa_withdrawal_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.mpesa_withdrawal');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] >= $amount && $ch['min'] <= $amount);

        if (!$charge) {
            throw new Exception('Mpesa withdrawal charge not found!');
        }

        return $charge['charge'];
    }
}

if (! function_exists('mpesa_collection_charge')) {
    /**
     * @throws Exception
     */
    function mpesa_collection_charge(int $amount): int
    {
        $paybillSwitchAmount = config('services.sidooh.providers.mpesa.pay_bill_switch_amount');
        if ($paybillSwitchAmount > 0 && $amount > $paybillSwitchAmount) {
            return pay_bill_charge($amount);
        }

        return buy_goods_charge($amount);
    }
}

if (! function_exists('admin_contacts')) {
    function admin_contacts(): array
    {
        return explode(',', config('services.sidooh.admin_contacts'));
    }
}
