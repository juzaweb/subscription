<?php

/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Providers;

use Illuminate\Support\Facades\File;
use Juzaweb\Modules\Core\Facades\Menu;
use Juzaweb\Modules\Core\Providers\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->booted(
            function () {
                if (File::missing(storage_path('app/installed'))) {
                    return;
                }

                $this->registerMenu();
            }
        );
    }

    public function register(): void
    {
        $this->registerTranslations();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerMenu(): void
    {
        Menu::make('subscription-methods', function () {
            return [
                'title' => __('Subscription Methods'),
                'parent' => 'settings',
            ];
        });
    }

    /**
     * Register translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'subscription');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    /**
     * Register views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/subscription');

        $sourcePath = __DIR__ . '/../resources/views';

        $this->publishes([$sourcePath => $viewPath], ['views', 'subscription-module-views']);

        $this->loadViewsFrom($sourcePath, 'subscription');
    }
}
