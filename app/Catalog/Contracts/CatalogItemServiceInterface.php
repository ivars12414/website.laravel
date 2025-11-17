<?php

namespace App\Catalog\Contracts;

use App\Models\Language;

interface CatalogItemServiceInterface
{
    public function findBySlugAndLanguage(string $slug, Language $language);
}
