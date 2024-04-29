# Subscription paymemt for Juzaweb CMS

## Features
- [x] Plan management
- [x] Paypal subscription
- [x] Payment history in profile page
- [x] Upgrade in profile page
- [x] Subscription management
- [x] Disabled ADs by plan (using Ads Manager plugin)
- [ ] Stripe payment
- [ ] Limit view posts feature

## Using

### Register module
To use this plugin, in Hook Action, you register the module using the subscription feature:
In demo plugin, we use `membership`

- Register menu Parent
```php
$this->hookAction->addAdminMenu(
    'subscription',
    'subscription',
    [
        'label' => trans('Subscription'),
    ]
);
```

- Register module to menu parent `subscription`
```php
$this->subscription->registerModule(
    'membership',
    [
        'label' => trans('Membership'),
        'menu' => [
            'label' => trans('Subscription'),
            'parent' => 'subscription',
        ]
    ]
);
```

**Register Module all options**

```php
$this->subscription->registerModule(
    'membership',
    [
        'label' => trans('Membership'),
        'menu' => [
            'label' => trans('Membership'),
            'parent' => 'subscription',
        ],
        'allow_plans' => true,
        'allow_payment_methods' => true,
        'allow_user_subscriptions' => true,
        'allow_payment_histories' => true,
        'allow_setting_page' => true,
    ]
);
```

### Register Features

```php
use Juzaweb\Subscription\Contrasts\Subscription;

app()->make(Subscription::class)->registerPlanFeature(
    'view_ads',
    [
        'label' => __('No Ads on website'),
        'module' => 'membership',
    ]
);
```

- Handle feature
```php
# Action Class
public function handle(): void
{
    if (plugin_enabled('juzaweb/ads-manager')) {
        $this->addFilter('jwad.can_show_ads', [$this, 'filterCanShowAds']);
    }
}

/**
 * A function that filters whether ads can be shown based on user subscription plan.
 *
 * @param mixed $canShowAds The current value indicating if ads can be shown.
 * @return bool
 */
public function filterCanShowAds($canShowAds): bool
{
    $user = request()?->user();

    if (!$user) {
        return $canShowAds;
    }

    $plan = subscripted_plan($user, 'membership');

    if (!$plan) {
        return $canShowAds;
    }

    $planFeature = $plan->features()
        ->where(['feature_key' => 'view_ads'])
        ->first();

    if (!$planFeature || $planFeature->value != 1) {
        return $canShowAds;
    }

    return false;
}
```