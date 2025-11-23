<?php

namespace App\Http\Controllers;

use App\RouteResolvers\Catalog\CatalogRouteResolver;
use App\Support\PageContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    protected CatalogRouteResolver $resolver;

    public function __construct(CatalogRouteResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(Request $request, PageContext $context)
    {
        $lang = $context->language();
        /** @var \App\RouteResolvers\Catalog\CatalogRouteContext|null $route */
        $route = $context->getSectionContext('catalog');
        if (!$route) {
            $route = $this->resolver->resolveCatalog($request, $lang, $context->section());
            $context->setSectionContext('catalog', $route);
        }

        if ($route->items instanceof Builder) {
            $route->items = $route->items
                ->paginate(12, ['*'], 'page', $route->page ?? 1)
                ->withQueryString();
        }

        if ($route->is404()) abort(404);
        if ($route->isItem() && !$route->item) abort(404);
        if ($route->isCategory() && !$route->category) abort(404);

        if ($route->isItem()) {
            $title = $route->item->getMetaTitle($lang->code) ?? $route->item->getName($lang->code);
            $context->meta('title', $title);
            $context->meta('h1', $title);
            $context->breadcrumbs($this->itemBreadcrumbs($route, $lang->code));
            return view('sections.catalog.item', ['ctx' => $route, 'item' => $route->item]);
        }

        if ($route->isCategory()) {
            $title = $route->category->getMetaTitle($lang->code) ?? $route->category->getName($lang->code);
            $context->meta('title', $title);
            $context->meta('h1', $title);
            $context->breadcrumbs($this->categoryBreadcrumbs($route, $lang->code));
            return view('sections.catalog.category', ['ctx' => $route]);
        }

        $context->breadcrumbs([
            ['title' => 'Home', 'url' => sectionHref()],
            ['title' => section()->name, 'url' => url()->current()],
        ]);

        return view('sections.catalog.category', ['ctx' => $route]);
    }

    protected function itemBreadcrumbs($ctx, string $lang): array
    {
        $bc = [
            ['title' => 'Home', 'url' => sectionHref()],
            ['title' => section()->name, 'url' => sectionHref('catalog', $ctx->language->id)],
        ];
        if ($ctx->category) {
            foreach ($ctx->category->getParentsChain($lang) as $cat) {
                $bc[] = ['title' => $cat->name, 'url' => $cat->link];
            }
            $bc[] = ['title' => $ctx->category->name, 'url' => $ctx->category->link];
        }
        $bc[] = ['title' => $ctx->item->name, 'url' => null];
        return $bc;
    }

    protected function categoryBreadcrumbs($ctx, string $lang): array
    {
        $bc = [
            ['title' => 'Home', 'url' => sectionHref()],
            ['title' => section()->name, 'url' => sectionHref('catalog', $ctx->language->id)],
        ];
        foreach ($ctx->category->getParentsChain($lang) as $cat) {
            $bc[] = ['title' => $cat->getName($lang), 'url' => $cat->link];
        }
        $bc[] = ['title' => $ctx->category->getName($lang), 'url' => null];
        return $bc;
    }
}
