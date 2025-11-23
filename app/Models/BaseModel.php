<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AutoCacheable;

class BaseModel extends Model
{

    use AutoCacheable;

    /**
     * даты в unix_timestamp
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * Для автоматического boot трейтов.
     * Напр. в трейте AutoCacheable метод bootAutoCacheable()
     * @throws \ReflectionException
     */
    protected static function boot(): void
    {
        parent::boot();

        // Авто-вызов bootXxx трейтов
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . (new \ReflectionClass($trait))->getShortName();

            if (method_exists($class, $method)) {
                forward_static_call([$class, $method]);
            }
        }
    }
}
