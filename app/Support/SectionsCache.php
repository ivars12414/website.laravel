<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Section;

class SectionsCache
{
    private static array $perLabelCache = [];
    private static array $perHashCache = [];
    private static array $perIdCache = [];
    private static array $perCodeCache = [];
    private static array $mainCache = [];

    private static function setInCache(array $data): void
    {
        $data['section_link'] = self::buildHref($data);
        self::$perLabelCache[$data['label']][$data['lang_id']] = $data;
        self::$perHashCache[$data['hash']][$data['lang_id']] = $data;
        self::$perIdCache[$data['id']] = $data;
        self::$perCodeCache[$data['code']][$data['lang_id']] = $data;

        if ((int)$data['main'] === 1 && (int)$data['status'] === 1) {
            self::$mainCache[$data['lang_id']] = $data;
        }
    }

    private static function buildHref(array $data): string
    {
        $languageCode = Language::find($data['lang_id'])?->code;

        if (!empty($data['scroll_href'])) {
            $uri = request()?->getRequestUri() ?? ($_SERVER['REQUEST_URI'] ?? '');
            $uri = explode('?', $uri)[0];
            $expUri = explode('/', $uri, 3);

            if (empty($expUri[1]) || (!isset($expUri[2]) && $expUri[1] === $languageCode)) {
                return $data['scroll_href'];
            }

            if ($data['main']) {
                return $data['lang_id'] === getMainLang() ? '/' : '/' . $languageCode;
            }

            $prefix = $data['lang_id'] === getMainLang() ? '/' : '/' . $languageCode;
            return $prefix . $data['scroll_href'];
        }

        if (!empty($data['ex_link'])) {
            return $data['ex_link'];
        }

        if ($data['main']) {
            return $data['lang_id'] === getMainLang() ? '/' : '/' . $languageCode;
        }

        return '/' . $languageCode . '/' . $data['code'];
    }

    public static function getByLabel(string $label, int $langId): array
    {
        if (empty($label) || empty($langId)) {
            return [];
        }

        if (!isset(self::$perLabelCache[$label][$langId])) {
            $data = Section::where('lang_id', $langId)->where('label', $label)->first()?->toArray();
            if ($data) {
                self::setInCache($data);
            }
        }

        return self::$perLabelCache[$label][$langId] ?? [];
    }

    public static function getById(int $id): array
    {
        if (empty($id)) {
            return [];
        }

        if (!isset(self::$perIdCache[$id])) {
            $data = Section::find($id)?->toArray();
            if ($data) {
                self::setInCache($data);
            }
        }

        return self::$perIdCache[$id] ?? [];
    }

    public static function getByCode(string $code, int $langId): array
    {
        if (empty($code) || empty($langId)) {
            return [];
        }

        if (!isset(self::$perCodeCache[$code][$langId])) {
            $data = Section::where('lang_id', $langId)->where('code', $code)->first()?->toArray();
            if ($data) {
                self::setInCache($data);
            }
        }

        return self::$perCodeCache[$code][$langId] ?? [];
    }

    public static function getByHash(string $hash, int $langId): array
    {
        if (empty($hash) || empty($langId)) {
            return [];
        }

        if (!isset(self::$perHashCache[$hash][$langId])) {
            $data = Section::where('lang_id', $langId)->where('hash', $hash)->first()?->toArray();
            if ($data) {
                self::setInCache($data);
            }
        }

        return self::$perHashCache[$hash][$langId] ?? [];
    }

    public static function getHrefByLabel(string $label, int $langId = 0): string
    {
        $langId = $langId ?: getMainLang();
        return self::getByLabel($label, $langId)['section_link'] ?? '';
    }

    public static function getHrefByHash(string $hash, int $langId = 0): string
    {
        $langId = $langId ?: getMainLang();
        return self::getByHash($hash, $langId)['section_link'] ?? '';
    }

    public static function getHrefById(int $id): string
    {
        return self::getById($id)['section_link'] ?? '';
    }

    public static function getMain(string $what, int $langId): string
    {
        if (!isset(self::$mainCache[$langId])) {
            $data = Section::where('status', '1')
                ->where('main', '1')
                ->where('lang_id', $langId)
                ->first()?->toArray();
            if ($data) {
                self::setInCache($data);
            }
        }

        return self::$mainCache[$langId][$what] ?? '';
    }
}
