<?php

namespace App\Models;

class Language extends BaseModel
{
    protected $table = 'langs';

    public $timestamps = false;

    protected $guarded = ['id'];

    public static function active()
    {
        return self::where('status', 1)->get();
    }

    public static function default()
    {
        return self::where('main', 1)->first();
    }
}
