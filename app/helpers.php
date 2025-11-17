<?php

use App\Models\Language;
use App\Models\Section;
use App\Models\Settings;
use App\Support\LC;
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

if (!function_exists('splitSearchTerms')) {
    function splitSearchTerms(?string $search): array
    {
        if (!is_string($search) || trim($search) === '') {
            return [];
        }

        preg_match_all('/"([^"]+)"|(\S+)/u', $search, $m);
        $parts = [];

        // $m[1] — совпадения в кавычках, $m[2] — одиночные слова
        foreach ($m[0] as $idx => $_) {
            $phrase = $m[1][$idx] ?? '';
            $word = $m[2][$idx] ?? '';
            $parts[] = $phrase !== '' ? $phrase : $word;
        }

        // Нормализуем пробелы внутри фраз
        return array_map(fn($t) => preg_replace('/\s+/u', ' ', trim($t)), $parts);
    }
}

function sectionHref($label = '', $lang_id = 0): string
{
    $lang_id = (!empty($lang_id)) ? $lang_id : lang()->id;
    if (empty($label)) {
        return getMainModule('section_link', (int)$lang_id);
    }
    if ($label === 'cabinet' && !getSectionDataBylabel('cabinet', $lang_id)['status']) {
        $label = 'settings';
    }
    return sectionsCache::getHrefByLabel($label, (int)$lang_id);
}

function getMainModule($what, $lang_id = 0)
{
    $lang_id = !empty($lang_id) ? $lang_id : lang()->id;
    return sectionsCache::getMain($what, (int)$lang_id);
}

function getSectionDataBylabel($label, $lang_id = 0)
{
    $lang_id = !empty($lang_id) ? $lang_id : lang()->id;
    return sectionsCache::getByLabel($label ?? '', (int)$lang_id);
}

function parseTextWithVars(string $text): string
{
    $text = preg_replace_callback('/%link\.([a-zA-Z0-9_]+)%/', function ($matches) {
        return sectionHref($matches[1]);
    }, $text);

    $text = preg_replace_callback('/%contacts\.([a-zA-Z0-9_]+)%/', function ($matches) {
        return getContacts($matches[1]);
    }, $text);

    $text = preg_replace_callback('/%config\.([a-zA-Z0-9_]+)%/', function ($matches) {
        return getConfig($matches[1]);
    }, $text);

    return $text;
}

function getContacts($field, $options = [])
{
    global $langId;

    //pre_dump($contactsInfoData);
    /* #fields
    Contacts:
        address1 address2
        city
        country
        email1 email2
        phone1 phone2
        fax1 fax2
        moore1 moore2
        coordinates
        c_skype1
        c_skype2
        grafik

    Socials:
        s_facebook
        s_twitter
        s_instagram
        s_youtube
        s_linkedin
        s_google
        s_pinterest
        s_vk
        s_ok
        s_draugiem

    Info for contacts Mod:
        about_name
        about_descr
    */
    if (!defined('CONTACTS_DATA')) {
        $lang_id = $langId > 0 ? $langId : getMainLang();
        define("CONTACTS_DATA", getWhat("contacts", "WHERE `lang_id` = '{$lang_id}'"));
    }
    if (!empty($options['nl2br'])) {
        return nl2br(CONTACTS_DATA[$field]);
    }
    return CONTACTS_DATA[$field];
}

function getWhat($from, $exp = false, $dump = false): ?array
{

    $modelClass = 'App\\Models\\' . \Illuminate\Support\Str::studly($from);

    if (!class_exists($modelClass)) {
        throw new \InvalidArgumentException("Model class [$modelClass] not found for table [$from]");
    }

    $query = $modelClass::query();

    if (!empty($exp)) {
        $exp = ltrim($exp, 'WHERE ');
        $query->whereRaw($exp);
    }

    if ($dump) {
        pre_dump($query->toRawSql());
    }

    return $query->first()?->toArray();
}

function getConfig(string $cfg)
{

    $langId = lang()->id;

    return Settings::where('deleted', '0')
        ->where('code', $cfg)
        ->where(function ($query) use ($langId) {
            $query->where('multi_lang', 0)
                ->orWhere(function ($q) use ($langId) {
                    $q->where('multi_lang', 1)
                        ->where('lang_id', $langId);
                });
        })
        ->value('value');

}

function getAdminLang(): int
{
    return (int)Language::where('admin', 1)->value('id');
}

function returnWord($code, $type = 99, $vars = [])
{
    global $langId, $translateLangId;

    $langId = $langId ?? context()->language()?->id ?? getMainLang();

    if (isset($_GET["table"]) || stristr($_SERVER['REQUEST_URI'], "/" . ADMIN_FOLDER . "/")) {
        $langId = getMainLang();
        $translateLangId = $langId;
        LC::getInstance()->setType(1);
    } else {
        $translateLangId = $langId;
        LC::getInstance()->setType($type);
    }

    if (!empty($vars)) {
        return str_replace(array_keys($vars), array_values($vars), LC::getInstance()->getOne($code));
    } else {
        return LC::getInstance()->getOne($code);
    }
}

include_once __DIR__ . '/catalog_helpers.php';
