<?php

namespace src\Facades;

use Illuminate\Support\Facades\Facade;

class Cloudflare extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cloudflare';
    }
}
