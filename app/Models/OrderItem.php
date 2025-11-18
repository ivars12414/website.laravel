<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
  protected $table = 'order_items';

  protected $guarded = ['id'];

  protected $casts = [
          'price' => 'float',
          'quantity' => 'integer',
          'discount' => 'float',
          'total' => 'float',
          'meta' => 'array',
          'meta_key' => 'string',
          'tax_rate' => 'float',
  ];

  /*** хотим, чтобы `$item->name` работал как поле */
  protected $appends = ['name'];

  /** аксессор */
  public function getNameAttribute(): ?string
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->name ?? null,
      default => null,
    };
  }

  public function getImageUrlAttribute(): ?string
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->imgUrlSmall ?? null,
      default => null,
    };
  }

  public function getLinkAttribute(): ?string
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->link ?? null,
      default => null,
    };
  }

  public function getVolumeAttribute(): ?int
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->volume ?? null,
      default => null,
    };
  }

  public function getDurationAttribute(): ?int
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->duration ?? null,
      default => null,
    };
  }

  public function getDescriptionAttribute(): ?string
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->description ?? null,
      default => null,
    };
  }

  public function getPackageSlugAttribute(): ?string
  {
    return match ($this->type) {
      'product', 'digital' => Item::findOrFail($this->entity_id)->package_slug ?? null,
      default => null,
    };
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  public function getTotalBeforeDiscount(): float
  {
    return $this->price * $this->quantity;
  }

  public function getDiscountAmount(): float
  {
    return $this->discount * $this->quantity;
  }

  public function getTaxAmount(): float
  {
    return ($this->price - $this->discount) * $this->quantity * $this->tax_rate;
  }

  public function getTotal(): float
  {
    return ($this->price - $this->discount) * $this->quantity + $this->getTaxAmount();
  }

  public function getDiscountedPrice(): float
  {
    return $this->price - $this->discount;
  }
}
