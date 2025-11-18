<?php

namespace App\Services;

use App\Services\Currency\CurrencyAmount;
use App\Services\Currency\CurrencyManager;

class CreditService
{
    public static function convert(float $amount, ?string $from = null, ?string $to = null, ?float $rate = null): float
    {
        $fromCurrency = CurrencyManager::get($from) ?? CurrencyManager::default();
        $toCurrency = CurrencyManager::get($to) ?? CurrencyManager::current() ?? CurrencyManager::default();

        if ($rate !== null) {
            return $amount * $rate;
        }

        return $amount * $toCurrency->value / $fromCurrency->value;
    }

    public static function format(
        float $amount,
        ?string $currencyCode = null,
        bool $withSymbol = true,
        bool $withStrongInt = true
    ): string {
        $currency = CurrencyManager::get($currencyCode) ?? CurrencyManager::current() ?? CurrencyManager::default();

        $formatted = number_format($amount, $currency->decimals, '.', $currency->thousands_separator);

        if (!$withSymbol) {
            return $formatted;
        }

        if ($withStrongInt) {
            [$int, $decimal] = explode('.', $formatted);
            $formatted = "<strong>{$int}.</strong>{$decimal}";
        }

        return match ($currency->symbol_position) {
            CurrencyAmount::SYM_LEFT => $currency->symbol . ' ' . $formatted,
            CurrencyAmount::SYM_RIGHT => $formatted . ' ' . $currency->symbol,
            default => $formatted,
        };
    }
}
