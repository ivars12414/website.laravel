<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\PageContext;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $context->breadcrumbs([
            ['title' => $context->section()->name, 'url' => url()->current()],
        ]);

        if (!$context->meta('title')) $context->meta('title', $context->section()->name);

        $main_categories = Category::whereActive()
            ->where('in_main', 1)
            ->withMin(['items as min_price' => function ($q) {
                $q->where('status', 1);
            }], 'price')
            ->get()
            ->map(function ($category) {
                $category->min_price = $category->min_price ?? 0;
                return $category;
            });

        return view('sections.main.index', ['context' => $context, 'main_categories' => $main_categories]);
    }
}
