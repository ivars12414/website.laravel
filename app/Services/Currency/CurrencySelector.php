<?php

namespace App\Services\Currency;

use App\Models\Currency;

class CurrencySelector
{
  public static function boot(): void
  {
    if (!isset($_SESSION['currency']) || empty($_SESSION['currency']['code'])) {
//      $currency = self::resolveCurrencyByGeo() ?? Currency::main();
      $currency = Currency::main();

      $_SESSION['currency'] = [
              'code' => $currency->code,
              'value' => $currency->value,
              'decimals' => $currency->decimals,
              'symbol' => $currency->symbol,
              'symbol_position' => $currency->symbol_position,
              'thousands_separator' => $currency->thousands_separator,
      ];
    }
  }

  protected static function resolveCurrencyByGeo(): ?Currency
  {
    $countryCode = self::detectCountryCode();
    if (!$countryCode) return null;

    return Currency::where('country_code', $countryCode)->first();
  }

  protected static function detectCountryCode(): ?string
  {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!$ip || $ip === '127.0.0.1') return null;

    // Пример: использование библиотеки geoip2/geoip2 (предпочтительно через DI)
    try {
      $reader = new \GeoIp2\Database\Reader(__DIR__ . '/../../storage/GeoLite2-Country.mmdb');
      $record = $reader->country($ip);
      return $record->country->isoCode ?? null;
    } catch (\Throwable $e) {
      return null;
    }
  }
}