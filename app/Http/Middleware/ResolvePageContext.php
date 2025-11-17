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
        if ($langCode) $language = Language::where('code', $langCode)->first();
        if (!$language) $language = Language::where('is_default', true)->first();
        if ($language && isset($segments[0]) && $segments[0] === $language->code) array_shift($segments);

        $context->setLanguage($language);
        if ($language) app()->setLocale($language->code);

        // Раздел
        $sectionCode = $segments[0] ?? null;
        $section = null;
        if ($sectionCode) $section = Section::where('code', $sectionCode)->first();
        if (!$section) $section = Section::where('code', 'home')->first();
        if ($section && $sectionCode) array_shift($segments);

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
