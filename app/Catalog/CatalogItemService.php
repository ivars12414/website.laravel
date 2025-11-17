<?php

namespace App\Catalog;

use App\Models\Language;

interface CatalogItemService
{
    public function findBySlugAndLanguage(string $slug, Language $language);
}
