<?php

namespace App\Providers;

use App\Catalog\Contracts\CatalogCategoryServiceInterface;
use App\Catalog\Contracts\CatalogItemServiceInterface;
use App\Catalog\Services\CatalogCategoryService;
use App\Catalog\Services\CatalogItemService;
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

        $this->app->bind(CatalogCategoryServiceInterface::class, CatalogCategoryService::class);
        $this->app->bind(CatalogItemServiceInterface::class, CatalogItemService::class);

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
        require_once base_path('bootstrap/tables_configs.php');
    }
}
