<?php

use App\Models\Language;
use App\Models\Section;
use App\Support\PageContext;
use App\Support\SectionsCache;

if (!function_exists('pre_dump')) {
    function pre_dump($str): void
    {
        if ((defined('DEV_MODE') && DEV_MODE) || config('app.debug')) {
            echo '<pre>';
            var_dump($str);
            echo '</pre>';
        }
    }
}

if (!function_exists('context')) {
    function context(): PageContext
    {
        return app(PageContext::class);
    }
}

if (!function_exists('lang')) {
    function lang(): ?Language
    {
        return context()->language();
    }
}

if (!function_exists('section')) {
    function section(): ?Section
    {
        return context()->section();
    }
}

if (!function_exists('getMainLang')) {
    function getMainLang(): int
    {
        return (int)(Language::default()?->id ?? 0);
    }
}

if (!function_exists('getSectionDataByHash')) {
    function getSectionDataByHash(string $hash, int $lang_id): array
    {
        return SectionsCache::getByHash($hash, $lang_id);
    }
}

if (!function_exists('sectionHrefByHash')) {
    function sectionHrefByHash($hash, $lang_id = 0): string
    {
        $lang_id = $lang_id > 0 ? $lang_id : (lang()?->id ?? getMainLang());
        if (!$lang_id) {
            return '';
        }

        $sectData = getSectionDataByHash($hash, $lang_id);
        if (($sectData['main'] ?? false) && $lang_id === getMainLang()) {
            return '/';
        }

        return SectionsCache::getHrefByHash($hash, (int)$lang_id);
    }
}
