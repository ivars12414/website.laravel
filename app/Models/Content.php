<?php

namespace App\Models;

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

      $section = Section::whereActive()->where('hash', $this->section_hash1)->where('lang_id', $lang_id)->first();
      return $section->getUrl() . "/" . $this->slug;
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
}
