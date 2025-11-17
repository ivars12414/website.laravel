<?php

namespace App\Seo;

use App\Models\Language;
use App\Support\PageContext;
use Illuminate\Http\Request;

class DefaultSectionSeoResolver implements SectionSeoResolverInterface
{
    public function supports($section): bool
    {
        return true;
    }

    public function resolve(Request $request, PageContext $context): void
    {
        $section = $context->section();
        $lang    = $context->language();
        if (!$section || !$lang) return;

        $segments = $request->segments();

        if (isset($segments[0]) && $segments[0] === $lang->code) array_shift($segments);
        if (isset($segments[0]) && $segments[0] === $section->code) array_shift($segments);
        $tail = implode('/', $segments);

        foreach (Language::all() as $l) {
            $url = url('/' . $l->code . '/' . $section->code . ($tail ? '/' . $tail : ''));
            $context->setAlternate($l->code, $url);
            if ($l->id === $lang->id) $context->setCanonical($url);
        }
    }
}
