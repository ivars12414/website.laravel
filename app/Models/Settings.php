<?php

namespace App\Models;

class Settings extends BaseModel
{
  protected $table = 'settings';

  public $timestamps = false;

  protected $guarded = ['id'];

}