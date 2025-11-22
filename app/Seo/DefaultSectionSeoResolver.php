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

        $this->routeResolver = app(TextRouteResolver::class);

        $ctx = $context->getSectionContext('text');
        if (!$ctx instanceof TextRouteContext) {
            $ctx = $this->routeResolver->resolve($request, $language, $section);
            $context->setSectionContext('text', $ctx);
        }

        $currentPath = $this->buildPathForLanguage($ctx, $language);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang, true);
            if ($alt) $context->setAlternate($lang->id, url($alt));
        }
    }

    protected function buildPathForLanguage(TextRouteContext $ctx, Language $lang, bool $dump = false): ?string
    {

        $sectionHref = sectionHrefByHash($ctx->section->getHash(), $lang->id);
        if (!$sectionHref) return null;

        switch ($ctx->type) {
            case TextRouteContext::TYPE_ITEM:
                // здесь нужно запрашивать ссылку на язык
                $slug = $ctx->item->slug;
                if (!$slug) return null;
                return $sectionHref . '/' . $slug;

            case TextRouteContext::TYPE_LIST:
                return $sectionHref;

            default:
                return null;
        }
    }

}
