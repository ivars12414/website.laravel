<?php

namespace App\Catalog\Contracts;

use App\Models\Language;

interface CatalogCategoryServiceInterface
{
    public function findByPathAndLanguage(array $pathSegments, Language $language);
}
