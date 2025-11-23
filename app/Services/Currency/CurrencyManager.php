<?php

namespace App\Services\Currency;

use App\Models\Currency;

class CurrencyManager
{
    protected static array $cache = [];

    public static function get(?string $code): ?Currency
    {
        if (empty($code)) return null;
        if (!isset(self::$cache[$code])) {
            self::$cache[$code] = Currency::byCodeCached($code);
        }
        return self::$cache[$code];
    }

    public static function current(): ?Currency
    {
        $code = session('currency.code') ?? Currency::mainCached()->code;

        return self::get($code);
    }

    public static function default(): ?Currency
    {
        return self::get(Currency::mainCached()->code);
    }
}
