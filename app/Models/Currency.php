<?php

namespace App\Models;

class Currency extends BaseModel
{
  protected $table = 'currencies';

  public $timestamps = false;

  protected $guarded = ['id'];

  public static function main(): Currency
  {
    return self::where('is_main', '1')->firstOrFail();
  }

  public static function allActive()
  {
    return self::where('status', '1')
            ->orderBy('ord')
            ->orderBy('code')
            ->get();
  }

}

