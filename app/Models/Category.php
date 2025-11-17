<?php

namespace App\Models;

use App\Contracts\HasLanguageLinks;
use App\Models\InfoBlocks\MainRegion;
use App\Models\Traits\MultiLanguageExternal;
use App\Models\Traits\WithStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel implements HasLanguageLinks
{
    protected $table = 'categories';

    use SoftDeletes;
    use WithStatus;
    use MultiLanguageExternal;

    protected array $multilingual = [
        'name',
        'description',
    ];

    protected $guarded = ['id'];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'items_categories');
    }

    protected function imgUrl(): Attribute
    {
        return Attribute::get(function () {
            return empty($this->img)
                ? '/cms_images/no-img-preview.png'
                : $this->img;
        });
    }

    protected function link(): Attribute
    {
        return Attribute::get(fn() => returnCategoryLink($this));
    }

    public static function fromLink(string $link): ?self
    {
        $segments = array_filter(explode('/', $link));
        $tree = [];
        $parentId = 0;

        foreach ($segments as $slug) {
            $category = self::whereActive()
                ->where('slug', trim($slug))
                ->where('parent_id', $parentId)
                ->first();

            if (!$category) {
                return null;
            }

            $tree[] = $category;
            $parentId = $category->id;
        }

        $last = end($tree);
        // Присваиваем дерево текущей категории
        $last->tree = collect($tree);

        return $last;
    }

    public static function visibleChildren(int $parentId, array $filters = []): Collection
    {
        $query = self::whereActive()
            ->where('parent_id', $parentId)
            ->whereHas('items', function ($q) {
                $q->where('status', 1);
            });
        return $query->get();
    }

    public static function filterable(int $langId): Collection
    {
        return self::whereActive()
            ->whereHas('items', function ($query) {
                $query->whereActive();
            })
            ->orderBy('ord')
            ->orderBy('name')
            ->get();
    }

    public static function getChildren(int $id): array
    {
        return self::whereActive()
            ->where('parent_id', $id)
            ->get()
            ->all(); // вернёт обычный массив объектов
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Scopes\SortByOrdScope);

        static::creating(function ($model) {
            if (is_null($model->ord)) {
                $model->ord = static::max('ord') + 1;
            }
        });
    }


    public static function allChildHashes(int $id): array
    {
        $result = [$id];

        $children = self::getChildren($id); // ← твоя функция получения детей

        foreach ($children as $child) {
            $result = array_merge($result, self::allChildHashes($child->id));
        }

        return $result;
    }

    public function getLanguageLink(int $langId): ?string
    {
        return $this->link;
    }

    public function getHash(): string
    {
        return $this->id;
    }

    public function getLangId(): int
    {
        return lang()->id;
    }

    public static function findByCountryCode(string $country_code)
    {
        return self::where('country_code', $country_code)->first();
    }

    public static function findBySlug(string $slug)
    {
        return self::where('slug', $slug)->first();
    }

}
