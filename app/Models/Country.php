<?php

namespace App\Models;

use App\Models\Traits\WithStatus;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
  use WithStatus;

  protected $table = 'countries_list';

  public $timestamps = false;

  public static function findByISO(string $iso, int $langId)
  {
    return static::where('iso', $iso)
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
