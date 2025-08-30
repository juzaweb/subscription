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

use Juzaweb\Core\Facades\Menu;
use Juzaweb\Core\Providers\ServiceProvider;
use Juzaweb\Modules\Subscription\Contracts\Subscription;
use Juzaweb\Modules\Subscription\SubscriptionManager;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app[Subscription::class]->registerDriver(
            'PayPal',
            function () {
                return new \Juzaweb\Modules\Subscription\Methods\PayPal();
            }
        );

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

        $this->app->singleton(
            Subscription::class,
            function ($app) {
                return new SubscriptionManager($app);
            }
        );
    }

    protected function registerMenu(): void
    {
        Menu::make('subscription-methods', __('Subscription Methods'))
            ->parent('settings');
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
        $viewPath = resource_path('views/modules/subscription');

        $sourcePath = __DIR__ . '/../resources/views';

        $this->publishes([$sourcePath => $viewPath], ['views', 'subscription-module-views']);

        $this->loadViewsFrom($sourcePath, 'subscription');
    }
}
