<?php

namespace App\Models\Traits;

trait WithOrd
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\SortByOrdScope);

        static::creating(function ($model) {
            if (is_null($model->ord)) {
                $model->ord = static::max('ord') + 1;
            }
        });
    }
}
