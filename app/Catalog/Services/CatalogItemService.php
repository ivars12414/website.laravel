<?php

namespace App\Catalog\Services;

use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Models\Language;
use App\Models\Item;

class CatalogItemService implements CatalogItemServiceInterface
{
    public function findBySlugAndLanguage(string $slug, Language $language)
    {
        return Item::whereActive()
            ->where('slug', $slug)
            ->first();
    }
}
