<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Providers;

use Juzaweb\Core\Facades\Menu;
use Juzaweb\Core\Providers\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->booted(
            function () {
                $this->registerMenu();
            }
        );
    }

    public function register(): void
    {
        $this->registerTranslations();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerMenu(): void
    {
        //
    }

    /**
     * Register translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'payment');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    /**
     * Register views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/payment');

        $sourcePath = __DIR__ . '/../resources/views';

        $this->publishes([$sourcePath => $viewPath], ['views', 'payment-module-views']);

        $this->loadViewsFrom($sourcePath, 'payment');
    }
}
