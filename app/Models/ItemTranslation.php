<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTranslation extends BaseModel
{
  protected $table = 'item_translations';

  public $timestamps = false;

  protected $guarded = ['id'];

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

}