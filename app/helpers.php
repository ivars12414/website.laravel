<?php

use App\Models\Language;
use Illuminate\Support\Str;

if (!function_exists('sectionHrefByHash')) {
    function sectionHrefByHash($hash, $lang_id = 0): string
    {
        $lang_id = ($lang_id > 0) ? $lang_id : lang()->id;
        $sectData = getSectionDataByHash($hash, $lang_id);
        if ($sectData['main'] && $lang_id == getMainLang()) {
            return '/';
        }
        return sectionsCache::getHrefByHash($hash, (int)$lang_id);
    }
}
