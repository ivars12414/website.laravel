<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Section;
use App\RouteResolvers\SectionContextResolver;
use App\Services\Currency\CurrencySelector;
use App\Services\SessionCodeResolver;
use App\Support\PageContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResolvePageContext
{
    public function handle(Request $request, Closure $next)
    {
        /** @var PageContext $context */
        $context = app(PageContext::class);

        $sessionCodeResolver = app(SessionCodeResolver::class);
        $context->setSessionCode($sessionCodeResolver->resolve($request));

        $currencySelector = app(CurrencySelector::class);
        $context->setCurrency($currencySelector->resolve($request));

        $segments = $request->segments();

        $language = $this->resolveLanguage($segments);
        $context->setLanguage($language);
        if ($language) app()->setLocale($language->code);

        $section = $this->resolveSection($segments, $language);
        if ($section) {
            $context->setSection($section);
        }

        if ($redirect = $this->ensureSectionAuth($section, $language)) {
            return $redirect;
        }

        $this->shareMenus($context);

        app(SectionContextResolver::class)->resolve($request);

        $this->setSectionBodyClass($context);

        view()->share('page', $context);

        return $next($request);
    }

    private function resolveLanguage(array &$segments): ?Language
    {
        $langCode = $segments[0] ?? null;
        $language = $langCode ? Language::byCodeCached($langCode) : null;

        if (!$language) {
            $language = Language::defaultCached();
        }

        if ($language && ($segments[0] ?? null) === $language->code) {
            array_shift($segments);
        }

        return $language;
    }

    private function resolveSection(array &$segments, ?Language $language): ?Section
    {
        $sectionCode = $segments[0] ?? null;
        $sectionQuery = Section::query();

        if ($language) {
            $sectionQuery->where('lang_id', $language->id);
        }

        $section = null;
        if ($sectionCode) {
            $section = (clone $sectionQuery)
                ->where(function ($q) use ($sectionCode) {
                    $q->where('code', $sectionCode);
                })
                ->first();
        }

        if (!$section) {
            $section = (clone $sectionQuery)
                ->where(function ($q) {
                    $q->where('main', '1');
                })
                ->first();
        }

        if ($section && $sectionCode) {
            array_shift($segments);
        }

        return $section;
    }

    private function ensureSectionAuth(?Section $section, ?Language $language): ?RedirectResponse
    {
        if ($section && ((int)$section->auth_required || (int)$section->position === Section::POSITION_CABINET) && !auth()->check()) {
            return redirect()->to(sectionHref('', $language?->id ?? 0));
        }

        return null;
    }

    private function shareMenus(PageContext $context): void
    {
        $langId = $context->language()?->id;
        $context->setMenus([
            'menu' => Section::whereActive()
                ->where('lang_id', $langId)
                ->where('hide_in_menu', '0')
                ->whereIn('position', [Section::POSITION_HEADER, Section::POSITION_HEADER_BOTTOM])
                ->orderBy('order_id')
                ->get(),
            'bottom_menu' => Section::whereActive()
                ->where('lang_id', $langId)
                ->where('hide_in_menu', '0')
                ->whereIn('position', [Section::POSITION_BOTTOM, Section::POSITION_HEADER_BOTTOM])
                ->orderBy('bottom_order_id')
                ->get(),
            'cabinet_menu' => Section::whereActive()
                ->where('lang_id', $langId)
                ->where('parent_id', 0)
                ->where('position', Section::POSITION_CABINET)
                ->where('hide_in_menu', '0')
                ->orderBy('order_id')
                ->get(),
        ]);
    }

    private function setSectionBodyClass(PageContext $context): void
    {
        $bodySectionTypesClasses = [
            'main' => 'main',
            'cabinet' => 'cabinet',
            'text' => 'inside',
            '404' => 'inside',
        ];

        $section = $context->section();
        if (!$section) {
            return;
        }

        if (!(int)$section->main) {
            if ((int)$section->position === Section::POSITION_CABINET) {
                $context->setBodyClass($bodySectionTypesClasses['cabinet']);
            } else {
                $context->setBodyClass($bodySectionTypesClasses['text']);
            }
        } else {
            $context->setBodyClass($bodySectionTypesClasses['main']);
        }
    }

}
