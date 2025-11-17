<?php

namespace App\Catalog\Services;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Models\Language;

class CatalogCategoryService implements CatalogCategoryServiceInterface
{
    public function findByPathAndLanguage(array $pathSegments, Language $language)
    {
        return null;
    }
}
