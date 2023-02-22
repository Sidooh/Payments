<?php

use App\Services\SidoohAccounts;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

if (! function_exists('ddj')) {
    #[NoReturn]
    function ddj(...$vars)
    {
        echo '<pre>';
        print_r($vars);
        exit;
    }
}

if (! function_exists('base_64_url_encode')) {
    function base_64_url_encode($text): array|string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
}

if (! function_exists('withRelation')) {
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    function withRelation($relation, $parentRecords, $parentKey, $childKey)
    {
        $childRecords = match ($relation) {
            'account' => SidoohAccounts::getAll(),
            default   => throw new BadRequestException('Invalid relation!')
        };

        $childRecords = collect($childRecords);

        return $parentRecords->transform(function($record) use ($parentKey, $relation, $childKey, $childRecords) {
            $record[$relation] = $childRecords->firstWhere($childKey, $record[$parentKey]);

            return $record;
        });
    }
}

if (! function_exists('withdrawal_charge')) {
    /**
     * @throws \Exception
     */
    function withdrawal_charge(int $amount): int
    {
        $charges = config('services.sidooh.charges.withdrawal');

        $charge = Arr::first($charges, fn ($ch) => $ch['max'] > $amount && $ch['min'] <= $amount);

        if (! $charge) {
            throw new Exception('Withdrawal charge not found!');
        }

        return $charge['charge'];
    }
}
