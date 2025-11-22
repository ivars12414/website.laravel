<?php

namespace App\Text;

use App\Models\Content;
use App\Models\Language;
use App\Models\Section;
use Illuminate\Http\Request;

class TextRouteResolver
{
    public function resolve(Request $request, Language $language, Section $section): TextRouteContext
    {
        $segments = $request->segments();

        if (isset($segments[0]) && $segments[0] === $language->code) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === $section->code) array_shift($segments);

        $page = max((int)$request->query('page', 1), 1);

        $query = Content::query()
            ->where('lang_id', $language->id)
            ->where('section_hash1', $section->hash)
            ->orderBy('id');

        $slug = $segments[0] ?? null;
        if ($slug) {
            $item = (clone $query)
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('hash', $slug);
                })
                ->first();

            $ctx = new TextRouteContext(TextRouteContext::TYPE_ITEM);
            $ctx->item = $item;
            $ctx->language = $language;
            $ctx->section = $section;
            $ctx->page = $page;
            return $ctx;
        }

        $count = (clone $query)->count();
        if ($count === 1) {
            $ctx = new TextRouteContext(TextRouteContext::TYPE_ITEM);
            $ctx->item = (clone $query)->first();
        } else {
            $ctx = new TextRouteContext(TextRouteContext::TYPE_LIST);
            $ctx->items = $query;
        }

        $ctx->language = $language;
        $ctx->section = $section;
        $ctx->page = $page;

        return $ctx;
    }
}
