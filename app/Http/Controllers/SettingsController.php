<?php

namespace App\Http\Controllers;

use App\Support\PageContext;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function handle(Request $request, PageContext $context)
    {
        $context->breadcrumbs([
            ['title' => $context->section()->name, 'url' => url()->current()],
        ]);

        if (!$context->meta('title')) $context->meta('title', $context->section()->name);

        return view('sections.cabinet.settings', ['context' => $context]);
    }
}
