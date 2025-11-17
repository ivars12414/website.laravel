<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'code','name','default_controller','requires_auth',
        'default_title','default_description','default_h1','meta_extra'
    ];

    protected $casts = [
        'requires_auth' => 'boolean',
        'meta_extra' => 'array',
    ];
}
