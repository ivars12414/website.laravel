<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemImage extends BaseModel
{
  protected $table = 'items_images';

  public $timestamps = false;

  protected $guarded = ['id'];

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

}