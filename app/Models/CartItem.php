<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
  protected $table = 'cart_items';

  protected $fillable = [
          'cart_id',
          'type',
          'entity_id',
          'image',
          'price',
          'quantity',
          'discount',
          'total',
          'meta',
          'meta_key',
  ];

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

  public function cart(): BelongsTo
  {
    return $this->belongsTo(Cart::class);
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
}
