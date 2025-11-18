<?php

namespace App\Models\Traits;

trait BelongsToClient
{
  public static function getClientColumn(): string
  {
    return property_exists(static::class, 'clientColumn')
            ? static::$clientColumn
            : 'client_id';
  }

  public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(\App\Models\Client::class, static::getClientColumn());
  }
}