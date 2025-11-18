<?php

namespace App\Services\Currency;

use App\Models\Currency;

class CurrencyManager
{
  protected static array $cache = [];

  public static function get(?string $code): ?Currency
  {
    if (!isset(self::$cache[$code])) {
      self::$cache[$code] = Currency::where('code', $code)->first();
    }
    return self::$cache[$code];
  }

  public static function current(): ?Currency
  {
    return self::get($_SESSION['currency']['code'] ?? Currency::main()->code);
  }

  public static function default(): ?Currency
  {
    return self::get(Currency::main()->code);
  }
}
