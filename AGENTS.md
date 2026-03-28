# AGENTS.md

## Overview
This repository contains the `juzaweb/subscription` module for Juzaweb CMS. It provides subscription payment support for applications built on top of Juzaweb CMS.

## Architecture
The module follows a typical Laravel package/module structure:
- **`src/`**: Contains the core logic of the module, including Models, Controllers (Http), Services, Providers, Exceptions, Enums, Contracts, and Methods.
  - **`src/Providers/`**: Service providers like `SubscriptionServiceProvider` register the module's services, migrations, translations, and views.
  - **`src/resources/`**: Contains language files and views specific to the module.
  - **`src/routes/`**: Defines the routes for the module.
- **`database/`**: Contains database migrations and seeders (often linked from `src/Providers`).
- **`tests/`**: Contains PHPUnit test cases for the module.
- **`helpers/`**: Contains global helper functions for the module.

## Key Technologies
- **PHP**: ^8.2
- **Framework**: Laravel (via Juzaweb CMS)
- **CMS**: Juzaweb CMS (`juzaweb/core` ^5.0)
- **Dependency Management**: Composer
- **Testing**: PHPUnit (`phpunit/phpunit` ^10.0), Orchestra Testbench (`orchestra/testbench` ^8.0|^9.0)

## Getting Started / Development Server

### 1. Dependency Installation
Install PHP dependencies via Composer:
```bash
composer install
```

### 2. Development Server
Since this is a module, it is designed to run within a Juzaweb CMS host application. To test it in a full environment:
1. Install a Juzaweb CMS application.
2. Link or require this module in the host application's `composer.json`.
3. Start the host application's development server:
   ```bash
   php artisan serve
   ```

### 3. Running Tests
You can run the module's test suite using PHPUnit:
```bash
vendor/bin/phpunit
```
