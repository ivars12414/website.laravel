<?php

namespace App\RouteResolvers\Catalog;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Models\Category;
use App\Models\Language;
use App\Models\Section;
use App\RouteResolvers\SectionContextResolverInterface;
use App\Support\PageContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogRouteResolver implements SectionContextResolverInterface
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

    public function supports(Section $section): bool
    {
        return $section->label === 'catalog';
    }

    public function resolve(Request $request, PageContext $context): void
    {
        $section = $context->section();
        $language = $context->language();
        if (!$section || !$language) return;

        /** @var CatalogRouteContext|null $ctx */
        $ctx = $context->getSectionContext('catalog');
        if (!$ctx instanceof CatalogRouteContext) {
            $ctx = $this->resolveCatalog($request, $language, $section);
            $context->setSectionContext('catalog', $ctx);
        }

        $currentPath = $this->buildPathForLanguage($ctx, $language, true);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang, $lang->id === 10);
            if ($alt) $context->setAlternate($lang->code, url($alt));
        }
    }

    protected function buildPathForLanguage(CatalogRouteContext $ctx, Language $lang, bool $dump = false): ?string
    {

        $sectionHref = sectionHrefByHash($ctx->section->getHash(), $lang->id);

        if (!$sectionHref) return null;

        switch ($ctx->type) {
            case CatalogRouteContext::TYPE_ITEM:
                $catPath = $ctx->category ? trim($ctx->category->getPath($lang->code), '/') : null;
                $slug = $ctx->item->getSlug($lang->code);
                if (!$slug) return null;
                return $sectionHref . ($catPath ? '/' . $catPath : '') . '/' . $slug;

            case CatalogRouteContext::TYPE_CATEGORY:
                $catPath = trim($ctx->category->link, '/');
//                if ($dump) dd($ctx->category);
                if (!$catPath) return null;
                return $sectionHref . $catPath;

            case CatalogRouteContext::TYPE_LIST:
                return $sectionHref;

            default:
                return null;
        }
    }

    public function resolveCatalog(Request $request, Language $language, Section $section): CatalogRouteContext
    {
        $segments = $request->segments();
        $langCode = $language->code;

        $hasCatalogSegments = false;

        $showSubcategoryItems = isConfig('show_subcat_items');
        $showCategories = isConfig('show_categories');

        if (isset($segments[0]) && $segments[0] === $langCode) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === section()->code) array_shift($segments);

        $hasCatalogSegments = !empty($segments);

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
            $ctx->section = $section;
            return $ctx;
        }

        $last = end($segments);
        $item = $this->itemService->findBySlugAndLanguage($last, $language);
        if ($item) {
            $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_ITEM);
            $ctx->item = $item;
            array_pop($segments);
            if (!empty($segments)) $ctx->category = $this->categoryService->findByPathAndLanguage($segments, $language);
            if (!empty($segments) && !$ctx->category) {
                return $this->makeNotFoundContext($language, $section);
            }
            $ctx->filters = $filters;
            $ctx->page = $page;
            $ctx->sort = $sort;
            $ctx->showSubcategoryItems = $showSubcategoryItems;
            $ctx->showCategories = $showCategories;
            $ctx->language = $language;
            $ctx->section = $section;
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
            $ctx->section = $section;
            return $ctx;
        }

        if ($hasCatalogSegments) {
            return $this->makeNotFoundContext($language, $section);
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
        $ctx->section = $section;
        return $ctx;
    }

    protected function makeNotFoundContext(Language $language, Section $section): CatalogRouteContext
    {
        $ctx = new CatalogRouteContext(CatalogRouteContext::TYPE_404);
        $ctx->language = $language;
        $ctx->section = $section;

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
