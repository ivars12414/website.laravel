<?php

namespace App\Models\Traits;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;
trait MultiLanguageExternal
{
  protected array $translationCache = [];
  protected bool $translationLoaded = false;

  public function getAttribute($key)
  {

    if (in_array($key, $this->multilingual ?? [])) {
      $translations = $this->getAllTranslations();

      $value = $translations[lang()->id][$key] ?? null;
      if ($value !== null && $value !== '') {
        return $value;
      }

      $fallbackValue = $translations[getMainLang()][$key] ?? null;
      if ($fallbackValue !== null && $fallbackValue !== '') {
        return $fallbackValue;
      }

      return null;
    }

    return parent::getAttribute($key);
  }

  public function __set($key, $value)
  {
    if (in_array($key, $this->multilingual ?? [])) {
      if (is_array($value)) {
        foreach ($value as $lang => $val) {
          $this->setTranslation($key, $lang, $val);
        }
      } else {
        $this->setTranslation($key, getMainLang(), $value);
      }
      $this->resetTranslationCache();
    } else {
      parent::__set($key, $value);
    }
  }

  public function __call($method, $args)
  {
    if (in_array($method, $this->multilingual ?? [])) {
      $lang = $args[0] ?? null;
      $value = $args[1] ?? null;

      if ($lang && $value !== null) {
        $this->resetTranslationCache();
        return $this->setTranslation($method, $lang, $value);
      } elseif ($lang) {
        return $this->getTranslated($method, $lang, false);
      }
    }

    return parent::__call($method, $args);
  }

  public function getTranslated(string $field, string $lang, bool $fallback = true): ?string
  {
    $translations = $this->getAllTranslations();

    return $translations[$lang][$field]
            ?? ($fallback ? $translations[getMainLang()][$field] ?? null : null);
  }

  public function setTranslation(string $field, string $lang, string $value): static
  {
    $table = $this->getTranslationTable();
    $modelId = $this->getKey();
    $foreignKey = $this->getTranslationForeignKey();

    $existing = DB::table($table)
            ->where($foreignKey, $modelId)
            ->where('lang_id', $lang)
            ->first();

    if ($existing) {
      DB::table($table)
              ->where('id', $existing->id)
              ->update([$field => $value]);
    } else {
      DB::table($table)->insert([
              $foreignKey => $modelId,
              'lang_id' => $lang,
              $field => $value,
      ]);
    }

    // Обновляем кэш для этого языка
    $this->translationCache[$lang][$field] = $value;

    return $this;
  }

  public function getAllTranslations(): array
  {
    if ($this->translationLoaded) {
      return $this->translationCache;
    }

    $this->translationCache = [];
    $this->translationLoaded = true;

    $table = $this->getTranslationTable();
    $modelId = $this->getKey();
    $foreignKey = $this->getTranslationForeignKey();

    $rows = DB::table($table)
            ->where($foreignKey, $modelId)
            ->get();

    foreach ($rows as $row) {
      $lang = $row->lang_id;
      foreach ($this->multilingual as $field) {
        $this->translationCache[$lang][$field] = $row->{$field} ?? null;
      }
    }

    return $this->translationCache;
  }

  public function resetTranslationCache(): void
  {
    $this->translationLoaded = false;
    $this->translationCache = [];
  }

  protected function getTranslationTable(): string
  {
    return Str::snake(class_basename($this)) . '_translations';
  }

  protected function getTranslationForeignKey(): string
  {
    return Str::snake(class_basename($this)) . '_id';
  }
}
