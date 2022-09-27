<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class LocalCarbon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'localcarbon';
    }
}
