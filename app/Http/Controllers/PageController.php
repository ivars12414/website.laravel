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

        $controller = $section->controller ?? \App\Http\Controllers\TextSectionController::class;

        return app()->call(
            '\\App\\Http\\Controllers\\' . $controller . '@handle',
            compact('request', 'context')
        );

    }
}
