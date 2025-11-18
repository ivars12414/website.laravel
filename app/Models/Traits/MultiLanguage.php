<?php

namespace App\Models\Traits;

/**
 * Указать мультиязычные поля в поле $multilingual.
 * Поля станут массивами [$langId => $value].
 * При получении значения мультиязычного поля мы получаем значение на текущем языке ($langId), напр. $model->name. (Если перевода нет, то смотрим на дефолтный язык)
 * Если нужно значение на конкретном языке, используем $model->name($langId). (Проверки по дефолтному языку не будет)
 * Если нужно значение на конкретном языке с проверкой дефолтного языка, используем $model->getTranslated('name', $langId).
 */
trait MultiLanguage
{
  public function getAttribute($key)
  {
    global $langId;

    if (in_array($key, $this->multilingual ?? [])) {
      $translations = $this->getAllTranslations($key);
      return $translations[$langId] ?? $translations[getMainLang()] ?? null;
    }

    return parent::getAttribute($key);
  }

  public function __set($key, $value)
  {
    if (in_array($key, $this->multilingual ?? [])) {
      if (is_array($value)) {
        $this->attributes[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
      } else {
        $this->setTranslation($key, getMainLang(), $value);
      }
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
        $this->setTranslation($method, $lang, $value);
        return $this; // для чейнинга
      } elseif ($lang) {
        return $this->getTranslated($method, $lang, false); // fallback выключен
      }
    }

    return parent::__call($method, $args);
  }

  public function getTranslated(string $field, string $lang, bool $fallback = true): ?string
  {
    $translations = $this->getAllTranslations($field);
    if (!$translations) {
      return null;
    }

    return $translations[$lang]
            ?? ($fallback ? $translations[getMainLang()] ?? null : null);
  }

  public function setTranslation(string $field, string $lang, string $value): static
  {
    $translations = $this->getAllTranslations($field) ?? [];
    $translations[$lang] = $value;
    $this->__set($field, $translations);
    return $this;
  }

  public function getAllTranslations(string $field): ?array
  {
    $translations = $this->attributes[$field] ?? null;

    if (is_string($translations)) {
      $translations = json_decode($translations, true);
    }

    return is_array($translations) ? $translations : [];
  }
}