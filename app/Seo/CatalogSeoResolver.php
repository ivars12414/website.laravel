<?php

namespace App\Seo;

use App\Catalog\CatalogRouteContext;
use App\Catalog\CatalogRouteResolver;
use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use Illuminate\Http\Request;

class CatalogSeoResolver implements SectionSeoResolverInterface
{
    protected CatalogRouteResolver $routeResolver;

    public function __construct(CatalogRouteResolver $routeResolver)
    {
        $this->routeResolver = $routeResolver;
    }

    public function supports(Section $section): bool
    {
        return $section->code === 'catalog';
    }

    public function resolve(Request $request, PageContext $context): void
    {
        $section  = $context->section();
        $language = $context->language();
        if (!$section || !$language) return;

        $ctx = $this->routeResolver->resolve($request, $language);

        $currentPath = $this->buildPathForLanguage($ctx, $language->code);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang->code);
            if ($alt) $context->setAlternate($lang->code, url($alt));
        }
    }

    protected function buildPathForLanguage(CatalogRouteContext $ctx, string $langCode): ?string
    {
        switch ($ctx->type) {
            case CatalogRouteContext::TYPE_ITEM:
                $catPath = $ctx->category ? trim($ctx->category->getPath($langCode), '/') : null;
                $slug = $ctx->item->getSlug($langCode);
                if (!$slug) return null;
                return '/' . $langCode . '/catalog' . ($catPath ? '/' . $catPath : '') . '/' . $slug;

            case CatalogRouteContext::TYPE_CATEGORY:
                $catPath = trim($ctx->category->getPath($langCode), '/');
                if (!$catPath) return null;
                return '/' . $langCode . '/catalog/' . $catPath;

            case CatalogRouteContext::TYPE_LIST:
                return '/' . $langCode . '/catalog';

            default:
                return null;
        }
    }
}
