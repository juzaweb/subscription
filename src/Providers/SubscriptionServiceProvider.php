<?php

namespace Juzaweb\Subscription\Providers;

use Juzaweb\CMS\Facades\ActionRegister;
use Juzaweb\CMS\Support\ServiceProvider;
use Juzaweb\Subscription\Actions\AjaxAction;
use Juzaweb\Subscription\Actions\MethodDefaultAction;
use Juzaweb\Subscription\Actions\ResourceAction;
use Juzaweb\Subscription\Repositories\PaymentHistoryRepository;
use Juzaweb\Subscription\Repositories\PaymentHistoryRepositoryEloquent;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PaymentMethodRepositoryEloquent;
use Juzaweb\Subscription\Repositories\PlanRepository;
use Juzaweb\Subscription\Repositories\PlanRepositoryEloquent;
use Juzaweb\Subscription\Repositories\UserSubscriptionRepository;
use Juzaweb\Subscription\Repositories\UserSubscriptionRepositoryEloquent;

class SubscriptionServiceProvider extends ServiceProvider
{
    public array $bindings = [
        PlanRepository::class => PlanRepositoryEloquent::class,
        PaymentMethodRepository::class => PaymentMethodRepositoryEloquent::class,
        UserSubscriptionRepository::class => UserSubscriptionRepositoryEloquent::class,
        PaymentHistoryRepository::class => PaymentHistoryRepositoryEloquent::class,
    ];

    public function boot(): void
    {
        ActionRegister::register([MethodDefaultAction::class, ResourceAction::class, AjaxAction::class]);
    }
}
