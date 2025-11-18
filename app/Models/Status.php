<?php

namespace App\Models;

class Status extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'statuses';

  // Указываем, что эта модель использует soft deletes
  use \Illuminate\Database\Eloquent\SoftDeletes;
  use \App\Models\Traits\MultiLanguage;
  use \App\Models\Traits\WithStatus;
  use \App\Models\Traits\WithOrd;

  protected $casts = [
          'next_statuses' => 'array',
  ];

  protected array $multilingual = [
          'name',
          'description',
  ];

  // Указываем заполняемые поля
  protected $fillable = [
          'category_id', 'label', 'name', 'status', 'is_negative', 'descr', 'ord', 'next_statuses'
  ];


  // Пример метода для поиска статуса по label
  public static function findByLabel(string $label, string $categoryLabel)
  {
    $category = StatusCategory::findByLabel($categoryLabel);
    return static::where('label', $label)
            ->where('category_id', $category->id)
            ->first();
  }

  public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(\App\Models\StatusCategory::class, 'category_id');
  }
}
