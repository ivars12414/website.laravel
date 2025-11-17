<?php

namespace App\Http\Controllers;

use App\Catalog\CatalogRouteResolver;
use App\Support\PageContext;
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
        $route = $this->resolver->resolve($request, $lang);

        if ($route->isItem() && !$route->item) abort(404);
        if ($route->isCategory() && !$route->category) abort(404);

        if ($route->isItem()) {
            $context->meta('title', $route->item->getMetaTitle($lang->code) ?? $route->item->getName($lang->code));
            $context->breadcrumbs($this->itemBreadcrumbs($route, $lang->code));
            return view('catalog.item', ['ctx' => $route]);
        }

        if ($route->isCategory()) {
            $context->meta('title', $route->category->getMetaTitle($lang->code) ?? $route->category->getName($lang->code));
            $context->breadcrumbs($this->categoryBreadcrumbs($route, $lang->code));
            return view('catalog.category', ['ctx' => $route]);
        }

        $context->meta('title', 'Catalog');
        $context->breadcrumbs([
            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
            ['title' => 'Catalog', 'url' => url()->current()],
        ]);

        return view('catalog.index', ['ctx' => $route]);
    }

    protected function itemBreadcrumbs($ctx, string $lang): array
    {
        $bc = [
            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
            ['title' => 'Catalog', 'url' => '/' . $lang . '/catalog'],
        ];
        if ($ctx->category) {
            foreach ($ctx->category->getParentsChain($lang) as $cat) {
                $bc[] = ['title' => $cat->getName($lang), 'url' => $cat->getUrl($lang)];
            }
        }
        $bc[] = ['title' => $ctx->item->getName($lang), 'url' => null];
        return $bc;
    }

    protected function categoryBreadcrumbs($ctx, string $lang): array
    {
        $bc = [
            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
            ['title' => 'Catalog', 'url' => '/' . $lang . '/catalog'],
        ];
        foreach ($ctx->category->getParentsChain($lang) as $cat) {
            $bc[] = ['title' => $cat->getName($lang), 'url' => $cat->getUrl($lang)];
        }
        $bc[] = ['title' => $ctx->category->getName($lang), 'url' => null];
        return $bc;
    }
}
