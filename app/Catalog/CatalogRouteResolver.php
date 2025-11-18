<?php

namespace App\Catalog;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogRouteResolver
{
    protected CatalogCategoryServiceInterface $categoryService;
    protected CatalogItemServiceInterface $itemService;

    public function __construct(
        CatalogCategoryServiceInterface $categoryService,
        CatalogItemServiceInterface     $itemService
    )
    {
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
    }

    public function resolve(Request $request, Language $language): CatalogRouteContext
    {
        $segments = $request->segments();
        $langCode = $language->code;

        $showSubcategoryItems = isConfig('show_subcat_items');
        $showCategories = isConfig('show_categories');

        if (isset($segments[0]) && $segments[0] === $langCode) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === section()->code) array_shift($segments);

        $filters = $request->query();
        $page = max((int)$request->query('page', 1), 1);
        $sort = $request->query('sort');

        if (empty($segments)) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_LIST);
            $ctx->filters = $filters;
            $ctx->page = $page;
            $ctx->sort = $sort;
            $ctx->showSubcategoryItems = $showSubcategoryItems;
            $ctx->showCategories = $showCategories;
            [$ctx->items, $ctx->filterOptions] = $this->prepareItems(null, $filters, $showSubcategoryItems);
            $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren(null, $filters) : null;
            $ctx->language = $language;
            return $ctx;
        }

        $last = end($segments);
        $item = $this->itemService->findBySlugAndLanguage($last, $language);
        if ($item) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_ITEM);
            $ctx->item = $item;
            array_pop($segments);
            if (!empty($segments)) $ctx->category = $this->categoryService->findByPathAndLanguage($segments, $language);
            $ctx->filters = $filters;
            $ctx->page = $page;
            $ctx->sort = $sort;
            $ctx->showSubcategoryItems = $showSubcategoryItems;
            $ctx->showCategories = $showCategories;
            $ctx->language = $language;
            return $ctx;
        }

        $category = $this->categoryService->findByPathAndLanguage($segments, $language);
        if ($category) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_CATEGORY);
            $ctx->category = $category;
            $ctx->filters = $filters;
            $ctx->page = $page;
            $ctx->sort = $sort;


            $ctx->showSubcategoryItems = $showSubcategoryItems;
            $ctx->showCategories = $showCategories;
            [$ctx->items, $ctx->filterOptions] = $this->prepareItems($category, $filters, $showSubcategoryItems);
            $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren($category, $filters) : null;
            $ctx->language = $language;
            return $ctx;
        }

        $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_LIST);
        $ctx->filters = $filters;
        $ctx->page = $page;
        $ctx->sort = $sort;
        $ctx->showSubcategoryItems = $showSubcategoryItems;
        $ctx->showCategories = $showCategories;
        [$ctx->items, $ctx->filterOptions] = $this->prepareItems(null, $filters, $showSubcategoryItems);
        $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren(null, $filters) : null;
        $ctx->language = $language;
        return $ctx;
    }

    protected function prepareItems(?Category $category, array $filters, bool $withSubcategories = false): array
    {
        $query = $this->categoryService->getItemsForCategory($category, $withSubcategories);

        $filterOptions = $this->collectFilterOptions($query);

        $this->applyFilters($query, $filters);

        $query->orderBy('price');

        return [$query, $filterOptions];
    }

    protected function collectFilterOptions(Builder $query): array
    {
        $durations = (clone $query)
            ->select('duration')
            ->whereNotNull('duration')
            ->distinct()
            ->orderBy('duration')
            ->pluck('duration');

        $volumes = (clone $query)
            ->select('volume')
            ->whereNotNull('volume')
            ->distinct()
            ->orderBy('volume')
            ->pluck('volume');

        $dataTypes = (clone $query)
            ->select('data_type')
            ->where('data_type', '>', 0)
            ->distinct()
            ->orderBy('data_type')
            ->pluck('data_type');

        return compact('durations', 'volumes', 'dataTypes');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['duration'])) {
            $query->where('items.duration', $filters['duration']);
        }

        if (!empty($filters['volume'])) {
            $query->where('items.volume', $filters['volume']);
        }

        if (!empty($filters['data_type'])) {
            $query->where('items.data_type', $filters['data_type']);
        }

        if (!empty($filters['search'])) {
            $query->search(trim($filters['search']));
        }
    }
}
