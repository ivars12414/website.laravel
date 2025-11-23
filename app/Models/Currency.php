<?php

namespace App\Models;

class Currency extends BaseModel
{
    protected $table = 'currencies';

    public $timestamps = false;

    protected $guarded = ['id'];

    public static function scopeMain(): Currency
    {
        return self::where('is_main', '1')->firstOrFail();
    }

    public static function allActive()
    {
        return self::where('status', '1')
            ->orderBy('ord')
            ->orderBy('code')
            ->get();
    }

    public static function mainCached(): ?self
    {
        return static::remember('main', [], function () {
            return static::query()->main()->first();
        });
    }

    public function scopeByCode($q, string $code)
    {
        return $q->where('code', $code);
    }

    public static function byCodeCached(string $code): ?self
    {
        return static::remember("code.$code", [], fn() => static::query()->byCode($code)->first());
    }

}

