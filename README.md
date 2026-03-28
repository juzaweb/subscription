# Juzaweb Subscription Module

[![Latest Version on Packagist](https://img.shields.io/packagist/v/juzaweb/subscription.svg?style=flat-square)](https://packagist.org/packages/juzaweb/subscription)
[![Total Downloads](https://img.shields.io/packagist/dt/juzaweb/subscription.svg?style=flat-square)](https://packagist.org/packages/juzaweb/subscription)

Subscription Payment Support module for [Juzaweb CMS](https://juzaweb.com/cms).

This module brings robust subscription management and payment gateway integration to your Juzaweb CMS project.

## Features

- **Subscription Management**: Easily manage user subscriptions directly from the admin panel.
- **Plans & Features**: Create customizable subscription plans and assign specific features to each plan.
- **Payment Gateway Integration**: Built-in support for PayPal subscription payments.
- **Webhooks Handling**: Automatically handle subscription events (e.g., payment success, cancellation, suspension) via webhooks.
- **Payment History**: Detailed logs and history of all subscription payments.
- **Multisite Support**: Fully compatible with Juzaweb CMS network/multisite features.

## Requirements

- PHP 8.2 or higher
- Juzaweb CMS Core ^5.0

## Installation

You can install the package via composer:

```bash
composer require juzaweb/subscription
```

After installing the package, publish the module assets, migrate the database, and activate it from your Juzaweb CMS admin panel.

```bash
php artisan module:migrate Subscription
```

## Usage

1. **Activate the Module:** Log into your Juzaweb admin panel, navigate to **Modules**, and activate the Subscription module.
2. **Configure Payment Methods:** Go to **Settings > Subscription Methods** to set up your payment gateways (e.g., configuring your PayPal Client ID, Secret, and Webhook ID).
3. **Create Plans:** Navigate to the **Subscriptions > Plans** section to create new subscription plans, set pricing, and assign features.
4. **Manage Subscriptions:** View and manage user subscriptions and payment histories directly from the dashboard.

## Testing

```bash
composer test
```

## Changelog

Please see [changelog.md](changelog.md) for more information on what has changed recently.

## License

The GNU V2 License. Please see [License File](LICENSE.md) for more information.
