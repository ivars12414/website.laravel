<?php

namespace App\Catalog\Contracts;

use App\Models\Language;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CatalogCategoryServiceInterface
{
    public function findByPathAndLanguage(array $pathSegments, Language $language);

    public function getItemsForCategory(?Category $category, bool $withSubcategories = false): Builder;

    public function getVisibleChildren(?Category $category, array $filters = []): Collection;
}
