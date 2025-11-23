<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

class Language extends BaseModel
{
    protected $table = 'langs';
    public $timestamps = false;
    protected $guarded = ['id'];

    // scopes
    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    public function scopeDefault($q)
    {
        return $q->where('main', 1);
    }

    // cached wrappers
    public static function activeCached(): Collection
    {
        return static::remember('active', [], function () {
            return static::query()->active()->get();
        });
    }

    public static function defaultCached(): ?self
    {
        return static::remember('default', [], function () {
            return static::query()->default()->first();
        });
    }

    public function scopeByCode($q, string $code)
    {
        return $q->where('code', $code);
    }

    public static function byCodeCached(string $code): ?self
    {
        return static::remember("code.$code", [], fn() => static::query()->active()->byCode($code)->first());
    }

    public static function byIdCached(int $id): ?self
    {
        return static::remember("id.$id", [], fn() => static::query()->active()->where('id', $id)->first());
    }

    // если хочешь другой TTL для конкретной модели:
    protected static int $cacheTtl = 3600;
}
