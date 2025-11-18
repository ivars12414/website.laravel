<?php

namespace App\Models;

class MailTemplate extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'mails_templates';
  public $timestamps = false;
  protected $guarded = [];
}
