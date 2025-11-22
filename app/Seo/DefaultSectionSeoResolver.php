<?php

namespace App\Seo;

use App\Models\Language;
use App\Support\PageContext;
use App\Text\TextRouteContext;
use App\Text\TextRouteResolver;
use Illuminate\Http\Request;

class DefaultSectionSeoResolver implements SectionSeoResolverInterface
{
    protected TextRouteResolver $routeResolver;

    public function supports($section): bool
    {
        return true;
    }

    public function resolve(Request $request, PageContext $context): void
    {
        $section = $context->section();
        $language = $context->language();
        if (!$section || !$language) return;


        $ctx = $context->getSectionContext('text');
        if (!$ctx instanceof TextRouteContext) {
            $ctx = $this->routeResolver->resolve($request, $language, $section);
            $context->setSectionContext('catalog', $ctx);
        }

        $currentPath = $this->buildPathForLanguage($ctx, $language->code);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang->code);
            if ($alt) $context->setAlternate($lang->code, url($alt));
        }
    }

    protected function buildPathForLanguage(TextRouteContext $ctx, string $langCode): ?string
    {
        switch ($ctx->type) {
            case TextRouteContext::TYPE_ITEM:
                $slug = $ctx->item->slug;
                if (!$slug) return null;
                return sectionHrefByHash($ctx->section->getHash(), Language::where('code', $langCode)->id) . '/' . $slug;

            case TextRouteContext::TYPE_LIST:
                return sectionHrefByHash($ctx->section->getHash(), Language::where('code', $langCode)->id);

            default:
                return null;
        }
    }

}
