<?php

namespace App\Http\Controllers;

use App\Support\PageContext;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $section = $context->section();
        abort_if(!$section, 404);

        $controller = $section->controller ?: 'TextSectionController';
        $controller = 'App\\Http\\Controllers\\' . ltrim($controller, '\\');

        return app()->call(
            $controller . '@handle',
            compact('request', 'context')
        );

    }
}
