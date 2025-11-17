<?php

namespace App\Catalog\Services;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Models\Language;
use App\Models\Category;

class CatalogCategoryService implements CatalogCategoryServiceInterface
{
    public function findByPathAndLanguage(array $pathSegments, Language $language)
    {
        $link = implode('/', array_filter($pathSegments));

        if ($link === '') {
            return null;
        }

        $category = Category::fromLink($link);

        if (!$category) {
            return null;
        }

        $segmentsCount = count(array_filter(explode('/', $link)));

        return $segmentsCount === $category->tree->count() ? $category : null;
    }
}
