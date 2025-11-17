<?php

use App\Models\Language;
use Illuminate\Support\Str;

if (!function_exists('sectionHrefByHash')) {
    function sectionHrefByHash(string $hash, int $langId): string
    {
        $language = Language::find($langId);
        $prefix = $language ? $language->code : $langId;
        $cleanHash = Str::startsWith($hash, '/') ? trim($hash, '/') : $hash;

        return url(sprintf('/%s/%s', $prefix, $cleanHash));
    }
}
