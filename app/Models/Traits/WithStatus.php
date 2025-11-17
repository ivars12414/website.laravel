<?php

namespace App\Models\Traits;

trait WithStatus
{
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function isActive(): bool
    {
        return (bool) ($this->status ?? false);
    }
}
