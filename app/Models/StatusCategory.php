<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusCategory extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'statuses_categories';

  // Указываем, что эта модель использует soft deletes
  use \Illuminate\Database\Eloquent\SoftDeletes;

  // Указываем заполняемые поля
  protected $fillable = [
          'name',
          'label',
          'ord',
  ];

  public function statuses(): HasMany
  {
    return $this->hasMany(Status::class, 'category_id');
  }

  // Пример метода для поиска по label
  public static function findByLabel(string $label)
  {
    return static::where('label', $label)->first();
  }
}
