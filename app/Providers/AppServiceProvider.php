<?php

namespace App\Providers;

use App\Catalog\CatalogCategoryService;
use App\Catalog\CatalogItemService;
use App\Catalog\CatalogRouteResolver;
use App\Seo\CatalogSeoResolver;
use App\Seo\DefaultSectionSeoResolver;
use App\Seo\SeoUrlManager;
use App\Support\PageContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PageContext::class, fn() => new PageContext());

        // бинды сервисов каталога — пока заглушки кидаем на анонимки, чтобы не падало
        $this->app->bind(CatalogCategoryService::class, function () {
            return new class implements CatalogCategoryService {
                public function findByPathAndLanguage(array $pathSegments, \App\Models\Language $language) { return null; }
            };
        });
        $this->app->bind(CatalogItemService::class, function () {
            return new class implements CatalogItemService {
                public function findBySlugAndLanguage(string $slug, \App\Models\Language $language) { return null; }
            };
        });

        $this->app->singleton(SeoUrlManager::class, function ($app) {
            return new SeoUrlManager(
                $app->make(PageContext::class),
                [
                    $app->make(CatalogSeoResolver::class),
                    // другие резолверы разделов добавишь здесь
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
