<?php

namespace App\Http\Controllers;

use App\Text\TextRouteContext;
use App\Text\TextRouteResolver;
use App\Support\PageContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TextSectionController extends Controller
{
    protected TextRouteResolver $resolver;

    public function __construct(TextRouteResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(Request $request, PageContext $context)
    {
        $section = $context->section();
        $language = $context->language();

        /** @var TextRouteContext|null $route */
        $route = $context->getSectionContext('text');
        if (!$route && $section && $language) {
            $route = $this->resolver->resolve($request, $language, $section);
            $context->setSectionContext('text', $route);
        }

        if (!$route) abort(404);

        if ($route->isList() && $route->items instanceof Builder) {
            $route->items = $route->items
                ->paginate(10, ['*'], 'page', $route->page ?? 1)
                ->withQueryString();
        }

        if (!$context->meta('title')) $context->meta('title', $section?->name);

        if ($route->isItem()) {
            if (!$route->item) abort(404);

            $title = $route->item->title ?? $route->item->name ?? $context->meta('title');
            $context->meta('title', $title);
            $context->meta('h1', $title);

            $context->breadcrumbs([
//            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
                ['title' => $section?->name, 'url' => sectionHref($section?->code, $language?->id)],
                ['title' => $title, 'url' => url()->current()],
            ]);

            return view('sections.text.index', ['page' => $context, 'ctx' => $route]);
        }

        $context->breadcrumbs([
//            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
            ['title' => $section?->name, 'url' => url()->current()],
        ]);

        return view('sections.text.index', ['page' => $context, 'ctx' => $route]);
    }
}
