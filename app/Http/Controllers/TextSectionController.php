<?php

namespace App\Http\Controllers;

use App\RouteResolvers\Default\DefaultSectionContextResolver;
use App\RouteResolvers\Default\DefaultRouteContext;
use App\Support\PageContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TextSectionController extends Controller
{

    protected DefaultSectionContextResolver $resolver;

    public function __construct(DefaultSectionContextResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(Request $request, PageContext $context)
    {
        $section = $context->section();
        $language = $context->language();

        /** @var DefaultRouteContext|null $route */
        $route = $context->getSectionContext('text');
        if (!$route && $section && $language) {
            $route = $this->resolver->resolveText($request, $language, $section);
            $context->setSectionContext('text', $route);
        }

        if (!$route || $route->is404()) abort(404);

        if ($route->isList() && $route->articles instanceof Builder) {
            $route->articles = $route->articles
                ->paginate(2, ['*'], 'page', $route->page ?? 1)
                ->withQueryString();
        }

        if (!$context->meta('title')) $context->meta('title', $section?->name);

        if ($route->isArticle()) {
            if (!$route->article) abort(404);

            $title = $route->article->title ?? $route->article->name ?? $context->meta('title');
            $context->meta('title', $title);
            $context->meta('h1', $title);

            $context->breadcrumbs([
                ['title' => 'Home', 'url' => sectionHref() ?? '/'],
                ['title' => $section?->name, 'url' => sectionHrefByHash($section->hash, $section->lang_id)],
                ['title' => $title, 'url' => url()->current()],
            ]);

            return view('sections.text.show', ['page' => $context, 'article' => $route->article]);
        }

        $context->breadcrumbs([
            ['title' => 'Home', 'url' => sectionHref() ?? '/'],
            ['title' => $section?->name, 'url' => url()->current()],
        ]);

        return view('sections.text.index', ['page' => $context, 'articles' => $route->articles]);
    }
}
