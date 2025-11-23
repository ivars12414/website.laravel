<?php

namespace App\RouteResolvers;

use App\Models\Section;
use App\Support\PageContext;
use Illuminate\Http\Request;

class SectionContextResolver
{
    protected array $resolvers;
    protected SectionContextResolverInterface $defaultResolver;
    protected PageContext $context;

    public function __construct(PageContext $context, array $resolvers = [])
    {
        $this->context = $context;
        $this->resolvers = $resolvers;
    }

    public function resolve(Request $request): void
    {
        $section = $this->context->section();
        if (!$section) return;

        $resolver = $this->findResolver($section);
        $resolver->resolve($request, $this->context);
    }

    protected function findResolver(Section $section): SectionContextResolverInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($section)) {
                return $resolver;
            }
        }
        return $this->defaultResolver;
    }
}
