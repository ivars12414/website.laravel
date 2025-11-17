<?php

namespace App\Catalog;

use App\Models\Language;
use Illuminate\Http\Request;

class CatalogRouteResolver
{
    protected CatalogCategoryService $categoryService;
    protected CatalogItemService $itemService;

    public function __construct(
        CatalogCategoryService $categoryService,
        CatalogItemService $itemService
    ) {
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
    }

    public function resolve(Request $request, Language $language): CatalogRouteContext
    {
        $segments = $request->segments();
        $langCode = $language->code;

        if (isset($segments[0]) && $segments[0] === $langCode) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === 'catalog') array_shift($segments);

        $filters = $request->query();
        $page = max((int)$request->query('page', 1), 1);
        $sort = $request->query('sort');

        if (empty($segments)) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_LIST);
            $ctx->filters = $filters; $ctx->page = $page; $ctx->sort = $sort;
            return $ctx;
        }

        $last = end($segments);
        $item = $this->itemService->findBySlugAndLanguage($last, $language);
        if ($item) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_ITEM);
            $ctx->item = $item;
            array_pop($segments);
            if (!empty($segments)) $ctx->category = $this->categoryService->findByPathAndLanguage($segments, $language);
            $ctx->filters = $filters; $ctx->page = $page; $ctx->sort = $sort;
            return $ctx;
        }

        $category = $this->categoryService->findByPathAndLanguage($segments, $language);
        if ($category) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_CATEGORY);
            $ctx->category = $category; $ctx->filters = $filters; $ctx->page = $page; $ctx->sort = $sort;
            return $ctx;
        }

        $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_LIST);
        $ctx->filters = $filters; $ctx->page = $page; $ctx->sort = $sort;
        return $ctx;
    }
}
