<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use App\Services\Currency\CurrencySelector;
use App\Services\SessionCodeResolver;
use App\Seo\SeoUrlManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolvePageContext
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('client');

        /** @var PageContext $context */
        $context = app(PageContext::class);

        $sessionCodeResolver = app(SessionCodeResolver::class);
        $context->setSessionCode($sessionCodeResolver->resolve($request));

        $currencySelector = app(CurrencySelector::class);
        $context->setCurrency($currencySelector->resolve($request));

        $segments = $request->segments();

        // Язык
        $langCode = $segments[0] ?? null;
        $language = null;
        if ($langCode) {
            $language = Language::where('status', 1)->where('code', $langCode)->first();
        }
        if (!$language) {
            $language = Language::default();
        }
        if ($language && isset($segments[0]) && $segments[0] === $language->code) {
            array_shift($segments);
        }

        $context->setLanguage($language);
        if ($language) app()->setLocale($language->code);

        // Раздел
        $sectionCode = $segments[0] ?? null;
        $section = null;
        $sectionQuery = Section::query();
        if ($language) {
            $sectionQuery->where('lang_id', $language->id);
        }

        if ($sectionCode) {
            $section = (clone $sectionQuery)
                ->where(function ($q) use ($sectionCode) {
                    $q->where('code', $sectionCode)->orWhere('hash', $sectionCode);
                })
                ->first();
        }

        if (!$section) {
            $section = (clone $sectionQuery)
                ->where(function ($q) {
                    $q->where('code', 'home')->orWhere('hash', 'home');
                })
                ->first();
        }

        if ($section && $sectionCode) {
            array_shift($segments);
        }

        if ($section) $context->setSection($section);

        // Auth для разделов с requires_auth или это раздел кабинета
        if ($section && ((int)$section->auth_required || (int)$section->position === Section::POSITION_CABINET) && !auth()->check()) {
            return redirect()->to(sectionHref('', $language?->id ?? 0));
        }

        // Меню
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

        $this->setSectionBodyClass($context);

        // SEO
        app(SeoUrlManager::class)->resolve($request);

        view()->share('page', $context);

        return $next($request);
    }

    private function setSectionBodyClass(PageContext $context): void
    {

        $body_section_labels_classes = [
            'news' => '',
        ];

        $body_section_types_classes = [
            'main' => 'main',
            'cabinet' => 'cabinet',
            'text' => 'inside',
            '404' => 'inside',
        ];

        if (!(int)$context->section()->main) {
            if ((int)$context->section()->position === SECTION_CABINET) {
                $context->setBodyClass($body_section_types_classes['cabinet']);
            } else {
                $context->setBodyClass($body_section_types_classes['text']);
            }
        } else {
            $context->setBodyClass($body_section_types_classes['main']);
        }

    }

}
