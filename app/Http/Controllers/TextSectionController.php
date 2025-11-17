<?php

namespace App\Http\Controllers;

use App\Support\PageContext;
use Illuminate\Http\Request;

class TextSectionController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $context->breadcrumbs([
            ['title' => 'Home', 'url' => route('home', absolute: false) ?? '/'],
            ['title' => $context->section()->name, 'url' => url()->current()],
        ]);

        if (!$context->meta('title')) $context->meta('title', $context->section()->name);

        return view('sections.text.index', ['context' => $context]);
    }
}
