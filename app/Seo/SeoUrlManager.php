<?php

namespace App\Seo;

use App\Support\PageContext;
use App\Models\Section;
use Illuminate\Http\Request;

class SeoUrlManager
{
    protected array $resolvers;
    protected SectionSeoResolverInterface $defaultResolver;
    protected PageContext $context;

    public function __construct(PageContext $context, array $resolvers = [])
    {
        $this->context = $context;
        $this->resolvers = $resolvers;
        $this->defaultResolver = new DefaultSectionSeoResolver();
    }

    public function resolve(Request $request): void
    {
        $section = $this->context->section();
        if (!$section) return;

        $resolver = $this->findResolver($section);
        $resolver->resolve($request, $this->context);
    }

    protected function findResolver(Section $section): SectionSeoResolverInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($section)) {
                return $resolver;
            }
        }
        return $this->defaultResolver;
    }
}
