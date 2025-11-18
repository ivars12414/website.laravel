<?php

namespace App\Models;

use App\Models\Traits\WithOrd;
use App\Models\Traits\WithStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
  use WithStatus;
  use WithOrd;

  protected $table = 'payment_methods';

  public $timestamps = false;
  
  protected $casts = [
          'order_types' => 'array',
  ];

  public static function findByLabel(string $label, int $langId)
  {
    return static::where('label', $label)
            ->where('lang_id', $langId)
            ->first();
//            ->toSql();
  }

  public static function findByHash(string $hash, int $langId)
  {
    return static::where('hash', $hash)
            ->where('lang_id', $langId)
            ->first();
//            ->toSql();
  }
}
