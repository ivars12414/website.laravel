<?php

namespace App\Text;

use App\Models\Content;
use App\Models\Language;
use App\Models\Section;

class TextRouteContext
{
    public const TYPE_LIST = 'list';
    public const TYPE_ITEM = 'item';

    public string $type;
    public ?Content $item = null;
    public $items = null;
    public ?int $page = null;
    public Language $language;
    public Section $section;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function isList(): bool
    {
        return $this->type === self::TYPE_LIST;
    }

    public function isItem(): bool
    {
        return $this->type === self::TYPE_ITEM;
    }
}
