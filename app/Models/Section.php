<?php

namespace App\Models;

use App\Contracts\HasLanguageLinks;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends BaseModel implements HasLanguageLinks
{
    use \App\Models\Traits\WithStatus;

    protected $table = 'sections';

    public $timestamps = false;

    protected $guarded = ['id'];

    const POSITION_HEADER = 0;
    const POSITION_BOTTOM = 1;
    const POSITION_HEADER_BOTTOM = 2;
    const POSITION_SYSTEM = 3;
    const POSITION_CABINET = 5;
    const POSITIONS = [
        self::POSITION_HEADER => [
            'title_code' => 'Main menu',
            'super' => 0,
            'in_list' => 1,
        ],
        self::POSITION_BOTTOM => [
            'title_code' => 'Bottom menu',
            'super' => 0,
            'in_list' => 1,
        ],
        self::POSITION_HEADER_BOTTOM => [
            'title_code' => 'Main menu and Bottom menu',
            'super' => 0,
            'in_list' => 0,
        ],
        self::POSITION_CABINET => [
            'title_code' => 'Cabinet',
            'super' => 0,
            'in_list' => 1,
        ],
        self::POSITION_SYSTEM => [
            'title_code' => 'System',
            'super' => 1,
            'in_list' => 1,
        ],
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }

    public function getUrl(?int $lang_id = null): string
    {
        $lang_id = $lang_id ?? $this->lang_id;
        return sectionHrefByHash($this->hash, $lang_id);
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

    public function getH1(): string
    {
        return !empty($this->name2) ? $this->name2 : ($this->name ?? '');
    }
}
