<?php

namespace App\Catalog;

use App\Models\Language;

interface CatalogCategoryService
{
    public function findByPathAndLanguage(array $pathSegments, Language $language);
}
