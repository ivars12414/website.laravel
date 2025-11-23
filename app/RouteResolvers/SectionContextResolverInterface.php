<?php

namespace App\RouteResolvers;

use App\Models\Section;
use App\Support\PageContext;
use Illuminate\Http\Request;

interface SectionContextResolverInterface
{
    public function supports(Section $section): bool;

    public function resolve(Request $request, PageContext $context): void;
}
