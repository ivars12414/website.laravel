<?php

namespace App\Http\Middleware;

use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use App\Seo\SeoUrlManager;
use Closure;
use Illuminate\Http\Request;

class ResolvePageContext
{
    public function handle(Request $request, Closure $next)
    {
        /** @var PageContext $context */
        $context = app(PageContext::class);

        $segments = $request->segments();

        // Язык
        $langCode = $segments[0] ?? null;
        $language = null;
        if ($langCode) {
            $language = Language::where('code', $langCode)->where('status', 1)->first();
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
        if ($sectionCode) {
            $section = Section::where('code', $sectionCode)
                ->when($language, fn($q) => $q->where('lang_id', $language->id))
                ->first();
        }
        if (!$section) {
            $section = Section::where('code', 'home')
                ->when($language, fn($q) => $q->where('lang_id', $language->id))
                ->first();
        }
        if ($section && $sectionCode) {
            array_shift($segments);
        }

        if ($section) $context->setSection($section);

        // Auth для разделов с requires_auth
        if ($section && $section->requires_auth && !auth()->check()) {
            return redirect()->route('login');
        }

        // SEO
        app(SeoUrlManager::class)->resolve($request);

        view()->share('page', $context);

        return $next($request);
    }
}
