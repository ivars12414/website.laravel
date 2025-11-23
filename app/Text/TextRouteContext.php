<?php

namespace App\Text;

use App\Models\Content;
use App\Models\Language;
use App\Models\Section;

class TextRouteContext
{
    public const TYPE_LIST = 'list';
    public const TYPE_ARTICLE = 'article';

    public string $type;
    public ?Content $article = null;
    public $articles = null;
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

    public function isArticle(): bool
    {
        return $this->type === self::TYPE_ARTICLE;
    }
}
