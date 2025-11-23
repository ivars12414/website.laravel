<?php

namespace App\Models;

use App\Support\SectionsCache;

class Content extends BaseModel implements \App\Contracts\HasLanguageLinks
{
    // Указываем имя таблицы в базе данных
    protected $table = 'content';
    public $timestamps = false;
    protected $guarded = [];

    public function getUrl(?int $lang_id = null): string
    {
        if (!empty($this->link)) {
            return $this->link;
        } else {
            $lang_id ??= lang()->id;

            $section = SectionsCache::getByHash($this->section_hash1, $lang_id);
            return $section['section_link'] . "/" . $this->slug;
        }
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getLangId(): int
    {
        return $this->lang_id;
    }

    public function getLanguageLink(int $langId): ?string
    {
        return $this->getUrl($langId);
    }

    public function getLanguageSlug(int $langId): ?string
    {
        return self::where('hash', $this->hash)
            ->where('lang_id', $langId)
            ->value('slug');
    }
}
