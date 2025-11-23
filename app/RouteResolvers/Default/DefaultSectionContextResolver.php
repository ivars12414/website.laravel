<?php

namespace App\RouteResolvers\Default;

use App\Models\Content;
use App\Models\Language;
use App\Models\Section;
use App\RouteResolvers\SectionContextResolverInterface;
use App\Support\PageContext;
use Illuminate\Http\Request;

class DefaultSectionContextResolver implements SectionContextResolverInterface
{
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
        if (!$ctx instanceof DefaultRouteContext) {
            $ctx = $this->resolveText($request, $language, $section);
            $context->setSectionContext('text', $ctx);
        }

        $currentPath = $this->buildPathForLanguage($ctx, $language);
        if (!$currentPath) return;

        $context->setCanonical(url($currentPath));

        foreach (Language::all() as $lang) {
            $alt = $this->buildPathForLanguage($ctx, $lang);
            if ($alt) $context->setAlternate($lang->code, url($alt));
        }
    }

    protected function buildPathForLanguage(DefaultRouteContext $ctx, Language $lang): ?string
    {

        $sectionHref = sectionHrefByHash($ctx->section->getHash(), $lang->id);
        if (!$sectionHref) return null;

        switch ($ctx->type) {
            case DefaultRouteContext::TYPE_ARTICLE:
                $slug = $ctx->article->getLanguageSlug($lang->id);
                if (!$slug) return null;
                return $sectionHref . '/' . $slug;

            case DefaultRouteContext::TYPE_LIST:
                return $sectionHref;

            default:
                return null;
        }
    }

    public function resolveText(Request $request, Language $language, Section $section): DefaultRouteContext
    {
        $segments = $request->segments();

        if (isset($segments[0]) && $segments[0] === $language->code) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === $section->code) array_shift($segments);

        $page = max((int)$request->query('page', 1), 1);

        $query = Content::query()
            ->where('lang_id', $language->id)
            ->where('section_hash1', $section->hash)
            ->where('status', '1')
            ->orderBy('id');

        $slug = $segments[0] ?? null;
        if ($slug) {
            $article = (clone $query)
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('hash', $slug);
                })
                ->first();

            $ctx = new DefaultRouteContext($article ? DefaultRouteContext::TYPE_ARTICLE : DefaultRouteContext::TYPE_404);
            $ctx->article = $article;
            $ctx->language = $language;
            $ctx->section = $section;
            $ctx->page = $page;
            return $ctx;
        }

        $count = (clone $query)->count();
        if ($count === 1) {
            $ctx = new DefaultRouteContext(DefaultRouteContext::TYPE_ARTICLE);
            $ctx->article = (clone $query)->first();
        } else {
            $ctx = new DefaultRouteContext(DefaultRouteContext::TYPE_LIST);
            $ctx->articles = $query;
        }

        $ctx->language = $language;
        $ctx->section = $section;
        $ctx->page = $page;

        return $ctx;
    }

}
