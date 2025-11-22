<?php

namespace App\Models;

class Contacts extends BaseModel
{
  protected $table = 'contacts_new';

  public $timestamps = false;

  protected $guarded = ['id'];
}