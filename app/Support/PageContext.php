<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Section;

class PageContext
{
    protected ?Language $language = null;
    protected ?Section $section = null;

    protected array $meta = [
        'title' => null,
        'description' => null,
        'h1' => null,
        'extra' => [],
    ];

    protected array $breadcrumbs = [];
    protected ?string $canonicalUrl = null;
    protected array $alternates = [];

    public function setLanguage(Language $language): void { $this->language = $language; }
    public function language(): ?Language { return $this->language; }

    public function setSection(Section $section): void
    {
        $this->section = $section;
        $this->meta['title']       = $section->default_title;
        $this->meta['description'] = $section->default_description;
        $this->meta['h1']          = $section->default_h1;
        $this->meta['extra']       = (array)($section->meta_extra ?? []);
    }
    public function section(): ?Section { return $this->section; }

    public function meta(string $key = null, $value = null)
    {
        if (is_null($key)) return $this->meta;
        if (is_null($value)) return $this->meta[$key] ?? null;
        $this->meta[$key] = $value; return $this;
    }

    public function setMetaArray(array $meta): void { $this->meta = array_replace($this->meta, $meta); }

    public function breadcrumbs(array $breadcrumbs = null)
    {
        if (is_null($breadcrumbs)) return $this->breadcrumbs;
        $this->breadcrumbs = $breadcrumbs;
    }

    public function addBreadcrumb(string $title, string $url = null): void
    {
        $this->breadcrumbs[] = compact('title','url');
    }

    public function setCanonical(string $url): void { $this->canonicalUrl = $url; }
    public function canonical(): ?string { return $this->canonicalUrl; }

    public function setAlternate(string $langCode, string $url): void { $this->alternates[$langCode] = $url; }
    public function alternates(): array { return $this->alternates; }
}
