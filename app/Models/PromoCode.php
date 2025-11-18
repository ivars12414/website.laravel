<?php

namespace App\Models;

use App\Models\Traits\MultiLanguageExternal;
use App\Models\Traits\WithOrd;
use App\Models\Traits\WithStatus;
use App\Services\Currency\CurrencyManager;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends BaseModel
{
  use WithStatus;
  use WithOrd;
  use SoftDeletes;
  use MultiLanguageExternal;

  protected $table = 'promo_codes';

  public const int TYPE_FIXED = 1;
  public const int TYPE_PERCENT = 2;

  const array TYPES = [
          self::TYPE_PERCENT => [
                  'title_code' => 'Percent (%)',
          ],
          self::TYPE_FIXED => [
                  'title_code' => 'Value (EUR)',
          ],
  ];

  protected $guarded = ['id'];

  protected $casts = [
          'value' => 'float',
          'active' => 'boolean',
          'conditions' => 'array',
  ];

  protected array $multilingual = [
          'name',
          'description'
  ];

  public static function findByCode(string $code)
  {
    return self::whereActive()->where('code', $code)->first();
  }

  public function applyToCart(Cart $cart): bool
  {
    if (!$this->status || $this->usedByClients()->where('client_id', $cart->user_id)->exists()) {
      $this->removeFromCart($cart);
      return false;
    }

    foreach ($cart->items as $item) {
      // Пример: условия не проверяются, логика может быть расширена
      if ($this->type === self::TYPE_FIXED) {
        $item->discount = $this->value / $cart->items->count();
      } elseif ($this->type === self::TYPE_PERCENT) {
        $item->discount = $item->price * ($this->value / 100);
      }
      $item->save();
    }

    $cart->promoCode()->associate($this);
    $cart->save();
    return true;
  }

  public function removeFromCart(Cart $cart): void
  {
    foreach ($cart->items as $item) {
      $item->discount = 0;
      $item->save();
    }
    $cart->promoCode()->dissociate();
    $cart->save();
  }

  public function getPercentValueFormatted(float $price_before_discount): string
  {
    if ($this->type === self::TYPE_PERCENT) return number_format($this->value) . '%';

    if ($this->type === self::TYPE_FIXED) {
      $percent = $this->value / $price_before_discount;
      return number_format($percent) . '%';
    }

    return '';
  }

  public function getFixedValue(float $price_before_discount): string
  {
    if ($this->type === self::TYPE_FIXED) return $this->value;

    if ($this->type === self::TYPE_PERCENT) {
      return $price_before_discount - ($price_before_discount * ($this->value / 100));
    }

    return '';
  }

  public function usedByClients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
  {
    return $this->belongsToMany(Client::class, 'clients_promo_codes');
  }
}
