<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
  protected $table = 'carts';

  public const string STATUS_DRAFT = 'draft';
  public const string STATUS_FROZEN = 'frozen';

  protected $fillable = [
          'status',
          'promo_code_id',
          'user_id',
          'session_code',
  ];

  protected $casts = [
          'user_id' => 'integer',
          'promo_code_id' => 'integer',
  ];


  public function items(): HasMany
  {
    return $this->hasMany(CartItem::class);
  }

  /**
   * @return Collection<CartItem>|null
   */
  public function getItemsCached(): ?Collection
  {
    static $items = null;
    if ($items === null) {
      $items = $this->items()->get(); // не ->items
    }
    return $items;
  }

  public function promoCode(): BelongsTo
  {
    return $this->belongsTo(PromoCode::class);
  }

  public function getSubtotal(): float
  {
    return $this->getItemsCached()->sum(fn($item) => $item->getTotalBeforeDiscount());
  }

  public function getDiscountTotal(): float
  {
    return $this->getItemsCached()->sum(fn($item) => $item->getDiscountAmount());
  }

  public function getTax(): float
  {
    return $this->getItemsCached()->sum(fn($item) => $item->getTaxAmount());
  }

  public function getTotal(): float
  {
    return $this->getItemsCached()->sum(fn($item) => $item->getTotal());
  }

  public function freeze(): void
  {
    $this->status = self::STATUS_FROZEN;
    $this->save();
  }
}