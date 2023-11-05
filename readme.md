# Subscription paymemt for Juzaweb CMS

## Using

### Register module

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
    'module_key',
    [
        'label' => trans('Subscription'),
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
    'module_key',
    [
        'label' => trans('Subscription'),
        'menu' => [
            'label' => trans('Subscription'),
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