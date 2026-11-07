<?php

namespace App\Providers;

use App\Models\GalleryImage;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Die Einstellungen der Website einmal pro Request laden – per Dependency Injection verfügbar.
        $this->app->scoped(SiteSetting::class, fn (): SiteSetting => SiteSetting::query()->firstOrCreate([], [
            'site_title' => "Bruno's Imbiss",
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        ViewFactory::composer(['components.layouts.site', 'pages.*'], function (View $view): void {
            $view->with('settings', $this->app->make(SiteSetting::class));
        });

        ViewFactory::composer('components.layouts.site', function (View $view): void {
            $view->with('galleryCount', GalleryImage::query()->visible()->count());
        });
    }
}
