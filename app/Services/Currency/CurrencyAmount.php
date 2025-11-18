<?php

namespace App\Services\Currency;

class CurrencyAmount
{
  protected float $amount;
  protected ?string $from;
  protected ?string $to;
  protected ?float $rate;

  public const string SYM_LEFT = 'left';
  public const string SYM_RIGHT = 'right';

  public function __construct(float $amount, ?string $from = null)
  {
    $this->amount = $amount;
    $this->from = $from;
    $this->to = null;
    $this->rate = null;
  }

  public function convert(?string $to = null, ?float $rate = null): self
  {
    $this->to = $to ?? CurrencyManager::current()->code;
    $this->rate = $rate;
    return $this;
  }

  public function format(bool $withSymbol = true, bool $withStrongInt = true): string
  {
    $fromCurrency = CurrencyManager::get($this->from) ?? CurrencyManager::default();
    $toCurrency = CurrencyManager::get($this->to) ?? $fromCurrency;

    if ($this->rate !== null) {
      // rate — прямой курс: сколько to за 1 from
      $converted = $this->amount * $this->rate;
    } else {
      // value — курс валют относительно базовой: переводим через базовую
      $converted = $this->amount * $toCurrency->value / $fromCurrency->value;
    }

    $formatted = number_format($converted, $toCurrency->decimals, '.', $toCurrency->thousands_separator);

    if (!$withSymbol) {
      return $formatted;
    }

    if ($withStrongInt) {
      list($int, $decimal) = explode('.', $formatted);
      $formatted = "<strong>{$int}.</strong>{$decimal}";
    }

    return match ($toCurrency->symbol_position) {
      self::SYM_LEFT => $toCurrency->symbol . ' ' . $formatted,
      self::SYM_RIGHT => $formatted . ' ' . $toCurrency->symbol,
      default => $formatted
    };
  }

  public function getAmount(): float
  {
    return $this->amount;
  }

}
