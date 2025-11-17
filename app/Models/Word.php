<?php


namespace App\Models;

class Word extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'words';
  public $timestamps = false;
  protected $guarded = ['id'];

}
