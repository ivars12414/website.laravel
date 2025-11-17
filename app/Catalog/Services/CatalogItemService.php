<?php

namespace App\Catalog\Services;

use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Models\Language;

class CatalogItemService implements CatalogItemServiceInterface
{
    public function findBySlugAndLanguage(string $slug, Language $language)
    {
        return null;
    }
}
