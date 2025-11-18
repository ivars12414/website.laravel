<?php


namespace App\Models;

class TableConfig extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'tables_configs';
  public $timestamps = false;
  protected $guarded = ['id'];

}
