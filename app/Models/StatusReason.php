<?php

namespace App\Models;

class StatusReason extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'statuses_reasons';

  // Указываем, что эта модель использует soft deletes
  use \Illuminate\Database\Eloquent\SoftDeletes;

  protected $guarded = [
          'id'
  ];

  // Пример метода для поиска статуса по ID
  public static function findByHash(string $hash, int $langId)
  {
    return static::where('hash', $hash)
            ->where('lang_id', $langId)
            ->first();
//            ->toSql();
  }

  public static function findByLabel(string $label, Status $status, int $lang_id = 0)
  {
    global $langId;
    return static::where('label', $label)
            ->where('status', $status->id)
            ->where('lang_id', $lang_id > 0 ? $lang_id : $langId)
            ->first();
  }
}
