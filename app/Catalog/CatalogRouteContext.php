<?php

namespace App\Catalog;

class CatalogRouteContext
{
    public const TYPE_LIST = 'list';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_ITEM = 'item';

    public string $type;
    public $category = null;
    public $item = null;
    public array $filters = [];
    public ?int $page = null;
    public ?string $sort = null;

    public function __construct(string $type) { $this->type = $type; }

    public function isList(): bool { return $this->type === self::TYPE_LIST; }
    public function isCategory(): bool { return $this->type === self::TYPE_CATEGORY; }
    public function isItem(): bool { return $this->type === self::TYPE_ITEM; }
}
