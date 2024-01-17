<?php

namespace Juzaweb\Subscription\Providers;

use Juzaweb\CMS\Contracts\GlobalDataContract;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Subscription\Actions\AjaxAction;
use Juzaweb\Subscription\Actions\MethodDefaultAction;
use Juzaweb\Subscription\Actions\PageDataAction;
use Juzaweb\Subscription\Actions\ResourceAction;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager as PaymentMethodManagerContrast;
use Juzaweb\Subscription\Contrasts\Subscription as SubscriptionContrast;
use Juzaweb\Subscription\Repositories\ModuleSubscriptionRepository;
use Juzaweb\Subscription\Repositories\ModuleSubscriptionRepositoryEloquent;
use Juzaweb\Subscription\Repositories\PaymentHistoryRepository;
use Juzaweb\Subscription\Repositories\PaymentHistoryRepositoryEloquent;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PaymentMethodRepositoryEloquent;
use Juzaweb\Subscription\Repositories\PlanRepository;
use Juzaweb\Subscription\Repositories\PlanRepositoryEloquent;
use Juzaweb\Subscription\Support\PaymentMethodManager;
use Juzaweb\Subscription\Support\Subscription;

class SubscriptionServiceProvider extends ServiceProvider
{
    public array $bindings = [
        PlanRepository::class => PlanRepositoryEloquent::class,
        PaymentMethodRepository::class => PaymentMethodRepositoryEloquent::class,
        PaymentHistoryRepository::class => PaymentHistoryRepositoryEloquent::class,
        ModuleSubscriptionRepository::class => ModuleSubscriptionRepositoryEloquent::class,
    ];

    public function boot(): void
    {
        ActionRegister::register(
            [
                MethodDefaultAction::class,
                ResourceAction::class,
                AjaxAction::class,
                PageDataAction::class,
            ]
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(
            PaymentMethodManagerContrast::class,
            fn ($app) => new PaymentMethodManager(
                $app[HookActionContract::class],
                $app[GlobalDataContract::class],
                $app[PaymentMethodRepository::class]
            )
        );

        $this->app->singleton(
            SubscriptionContrast::class,
            Subscription::class
        );
    }
}
