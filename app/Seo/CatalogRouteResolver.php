<?php

namespace App\Seo;

use App\Catalog\CatalogRouteContext;
use App\Catalog\CatalogRouteResolver as BaseCatalogRouteResolver;
use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use Illuminate\Http\Request;

class CatalogRouteResolver implements SectionSeoResolverInterface
{
    protected BaseCatalogRouteResolver $routeResolver;

    public function __construct(BaseCatalogRouteResolver $routeResolver)
    {
        $this->routeResolver = $routeResolver;
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
            $ctx = $this->routeResolver->resolve($request, $language, $section);
            $context->setSectionContext('catalog', $ctx);
        }

        $currentPath = $this->buildPathForLanguage($ctx, $language);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang);
            if ($alt) $context->setAlternate($lang->code, url($alt));
        }
    }

    protected function buildPathForLanguage(CatalogRouteContext $ctx, Language $lang): ?string
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
                $catPath = trim($ctx->category->getPath($lang->code), '/');
                if (!$catPath) return null;
                return $sectionHref . $catPath;

            case CatalogRouteContext::TYPE_LIST:
                return $sectionHref;

            default:
                return null;
        }
    }
}
