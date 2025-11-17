<?php

namespace App\Catalog;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Models\Language;
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
            $ctx->items = $this->categoryService->getItemsForCategory(null, $showSubcategoryItems);
            $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren(null, $filters) : null;
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
            $ctx->items = $this->categoryService->getItemsForCategory($category, $showSubcategoryItems);
            $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren($category, $filters) : null;
            return $ctx;
        }

        $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_LIST);
        $ctx->filters = $filters;
        $ctx->page = $page;
        $ctx->sort = $sort;
        $ctx->showSubcategoryItems = $showSubcategoryItems;
        $ctx->showCategories = $showCategories;
        $ctx->items = $this->categoryService->getItemsForCategory(null, $showSubcategoryItems);
        $ctx->categories = $showCategories ? $this->categoryService->getVisibleChildren(null, $filters) : null;
        return $ctx;
    }
}
