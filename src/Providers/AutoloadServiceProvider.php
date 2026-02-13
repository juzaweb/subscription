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

use Illuminate\Support\Facades\Route;
use Juzaweb\Modules\Core\Facades\Locale;
use Juzaweb\Modules\Core\Providers\ServiceProvider;
use Juzaweb\Modules\Subscription\Contracts\Subscription;
use Juzaweb\Modules\Subscription\Services\SubscriptionManager;
use Juzaweb\Modules\Subscription\Services\TestSubscription;

class AutoloadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app[Subscription::class]->registerDriver(
            'PayPal',
            function () {
                return new \Juzaweb\Modules\Subscription\Methods\PayPal();
            }
        );

        $this->app[Subscription::class]->registerModule(
            'test',
            function () {
                return new TestSubscription();
            }
        );

        $this->booted(
            function () {
                Route::middleware(['theme'])
                    ->prefix(Locale::setLocale())
                    ->group(__DIR__ . '/../routes/subscribe.php');
            }
        );
    }

    public function register()
    {
        $this->app->singleton(
            Subscription::class,
            function ($app) {
                return new SubscriptionManager($app);
            }
        );
    }
}
