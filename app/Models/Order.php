<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use App\Services\Currency\CurrencyManager;
use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
  use BelongsToClient;

  protected static $clientColumn = 'user_id';

  protected $table = 'orders';

  protected $guarded = ['id'];

  protected $casts = [
          'user_id' => 'integer',
  ];

  // инстанс-свойство, не static
  protected $itemsCache = null;

  protected static function boot(): void
  {
    parent::boot();

    static::creating(function ($order) {
      $order->nr = self::generateOrderNumber();
      $order->lang_id = lang()->id;
    });
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(Status::class);
  }

  public function payments()
  {
    return $this->morphMany(Payment::class, 'order', 'type');
  }

  /**
   * @return \Illuminate\Support\Collection
   */
  public function getItemsCached()
  {
    if ($this->itemsCache === null) {
      // грузим все позиции этого конкретного ордера
      $this->itemsCache = $this->items()->get();
    }
    return $this->itemsCache;
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

  public function getFullName(): string
  {
    return trim("$this->name $this->surname");
  }

  protected static function generateOrderNumber(): string
  {
    do {

      $number = generateNumberString(10);

    } while (self::where('nr', $number)->exists());

    return $number;
  }

  public function promoCode(): BelongsTo
  {
    return $this->belongsTo(PromoCode::class);
  }

  /**
   * @return Collection<StatusLog>|null
   */
  public function getStatusLogs(): ?Collection
  {
    return \App\Models\StatusLog::category(\App\Models\StatusCategory::findByLabel('orders'))->where('order_id', $this->id)->orderBy('id', 'desc')->get();
  }
}