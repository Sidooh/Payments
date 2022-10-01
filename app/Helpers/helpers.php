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
