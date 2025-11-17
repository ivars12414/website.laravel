<?php

namespace App\Models;

use App\Contracts\HasLanguageLinks;
use App\Models\Traits\MultiLanguageExternal;
use App\Models\Traits\WithStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends BaseModel implements HasLanguageLinks
{
    protected $table = 'items';

    use SoftDeletes;
    use WithStatus;
    use MultiLanguageExternal;

    protected $guarded = ['id'];

    protected array $multilingual = [
        'name',
        'description',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'items_categories')->withPivot(['is_main', 'ord']);
    }

    // Главная категория как ОТНОШЕНИЕ (можно eager-load'ить)
    public function mainCategory()
    {
        return $this->belongsToMany(Category::class, 'items_categories')
            ->wherePivot('is_main', true)
            ->withPivot('is_main', 'ord')
            ->withTimestamps()
            ->limit(1);
    }

    // Удобный аксессор: $item->main_category
    public function getMainCategoryAttribute()
    {
        return $this->categories()->wherePivot('is_main', true)->first();
    }

    // Утилиты
    public function setMainCategory(int $categoryId): void
    {
        // снять флаг со всех
        $this->categories()->updateExistingPivot(
            $this->categories()->pluck('categories.id'),
            ['is_main' => false]
        );
        // поставить флаг на нужной
        $this->categories()->syncWithoutDetaching([$categoryId => ['is_main' => true]]);
    }

    public function clearMainCategory(): void
    {
        $this->categories()->updateExistingPivot(
            $this->categories()->pluck('categories.id'),
            ['is_main' => false]
        );
    }

    public function images(): hasMany
    {
        return $this->hasMany(ItemImage::class);
    }

    public static function findBySlug(string $slug): ?Item
    {
        return self::whereActive()
            ->where('slug', $slug)
            ->first();
    }

    public static function forCategory(?Category $category): Builder
    {
        return static::whereActive()
            ->when($category, function ($query) use ($category) {
                // если категория передана — товары, связанные с ней
                $query->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });
            }, function ($query) {
                // если категория не передана — товары без связей
                $query->whereDoesntHave('categories');
            });
    }

    public function similarItems(int $limit = 5): Collection
    {
        return self::whereActive()
            ->where('id', '!=', $this->id)
            ->whereHas('categories', function ($q) {
                $q->whereIn('categories.id', $this->categories->pluck('id'));
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    protected function imgUrl(): Attribute
    {
        return Attribute::get(function () {
            return !empty($this->operatorImage->img)
                ? $this->operatorImage->img
                : (empty($this->img)
                    ? '/cms_images/no-img-preview.png'
                    : $this->img);
        });
    }

    protected function imgUrlSmall(): Attribute
    {
        return Attribute::get(function () {
            return !empty($this->operatorImage->img)
                ? $this->operatorImage->img
                : (empty($this->thumb)
                    ? '/cms_images/no-img-preview.png'
                    : $this->thumb);
        });
    }

    protected function link(): Attribute
    {
        return Attribute::get(fn() => returnItemLink($this));
    }

    public function getHash(): string
    {
        return $this->id;
    }

    public function getLangId(): int
    {
        return lang()->id;
    }

    public function getLanguageLink(int $langId): ?string
    {
        return $this->link;
    }

    public function collectPhotos(): array
    {
        $photos = [[
            'small' => $this->imgUrlSmall,
            'big' => $this->imgUrl
        ]];

        foreach ($this->images as $image) {
            $photos[] = [
                'small' => $image->thumb,
                'big' => $image->img
            ];
        }

        return $photos;
    }

    public function translations(): hasMany
    {
        return $this->hasMany(ItemTranslation::class);
    }

    /**
     * Поиск по словам (и фразам в кавычках) в translations.name/description и slug.
     *
     * @param Builder $query
     * @param string|null $search Строка запроса. Поддерживает фразы в "кавычках".
     * @param string $mode 'and' (все слова) или 'or' (любое слово)
     * @param int $minLen Минимальная длина термина
     */
    public function scopeSearch(Builder $query, ?string $search, string $mode = 'and', int $minLen = 2): Builder
    {
        $terms = collect(splitSearchTerms($search))
            ->map(fn($t) => trim($t))
            ->filter(fn($t) => mb_strlen($t) >= $minLen)
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return $query;
        }

        // экранирование LIKE + добавляем ESCAPE '\\'
        $esc = static function (string $v): string {
            return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $v);
        };

        // AND: каждый термин должен где-то совпасть
        if (strtolower($mode) === 'and') {
            foreach ($terms as $term) {
                $query->where(function (Builder $q) use ($term, $esc) {
                    $q->whereHas('translations', function (Builder $qt) use ($term, $esc) {
                        $term = $esc($term);
                        $qt->where('name', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%");
                    })->orWhere('slug', 'like', "%{$term}%");
                });
            }
            return $query;
        }

        // OR: достаточно совпадения по любому термину
        return $query->where(function (Builder $q) use ($terms, $esc) {
            foreach ($terms as $i => $term) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method}(function (Builder $qq) use ($term, $esc) {
                    $qq->whereHas('translations', function (Builder $qt) use ($term, $esc) {
                        $term = $esc($term);
                        $qt->where('name', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%");
                    })->orWhere('slug', 'like', "%{$term}%");
                });
            }
        });
    }

}
