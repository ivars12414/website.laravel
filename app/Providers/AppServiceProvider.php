<?php

namespace App\Providers;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Catalog\Services\CatalogCategoryService;
use App\Catalog\Services\CatalogItemService;
use App\Seo\CatalogRouteResolver;
use App\Seo\DefaultSectionContextResolver;
use App\Seo\SectionContextResolver;
use App\Support\PageContext;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PageContext::class, fn() => new PageContext());

        $this->app->bind(CatalogCategoryServiceInterface::class, CatalogCategoryService::class);
        $this->app->bind(CatalogItemServiceInterface::class, CatalogItemService::class);

        $this->app->singleton(SectionContextResolver::class, function ($app) {
            return new SectionContextResolver(
                $app->make(PageContext::class),
                [
                    $app->make(CatalogRouteResolver::class),
                    $app->make(DefaultSectionContextResolver::class),
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
        Schema::defaultStringLength(191);
        require_once base_path('bootstrap/tables_configs.php');
    }
}
