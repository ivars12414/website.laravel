<?php

namespace App\Catalog\Services;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Models\Language;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function getItemsForCategory(?Category $category, bool $withSubcategories = false): Builder
    {
        if ($withSubcategories) {
            $ids = Category::allChildHashes($category?->id ?? 0);

            return Item::whereActive()
                ->whereHas('categories', function ($q) use ($ids) {
                    $q->whereIn('categories.id', $ids);
                });
        }

        return Item::forCategory($category);
    }

    public function getVisibleChildren(?Category $category, array $filters = []): Collection
    {
        $parentId = $category?->id ?? 0;

        return Category::visibleChildren($parentId, $filters);
    }
}
