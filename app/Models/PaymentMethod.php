<?php

namespace App\Models;

use App\Models\Traits\MultiLanguageExternal;
use App\Models\Traits\WithOrd;
use App\Models\Traits\WithStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
  use WithStatus;
  use WithOrd;
  use MultiLanguageExternal;

  protected $table = 'payment_methods';

  public $timestamps = false;
  
  protected $casts = [
          'order_types' => 'array',
  ];

  protected array $multilingual = [
          'name',
          'descr',
          'btn_txt',
  ];

  public static function findByLabel(string $label)
  {
    return static::where('label', $label)->first();
  }
}
