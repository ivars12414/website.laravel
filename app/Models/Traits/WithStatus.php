<?php

namespace App\Models\Traits;

trait WithStatus
{
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    public function activate(): void
    {
        $this->status = 1;
        $this->save();
    }

    public function deactivate(): void
    {
        $this->status = 0;
        $this->save();
    }
}
