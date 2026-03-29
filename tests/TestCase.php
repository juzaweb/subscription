<?php

namespace Juzaweb\Modules\Subscription\Tests;

use Illuminate\Foundation\Application;
use Juzaweb\Hooks\HooksServiceProvider;
use Juzaweb\Modules\Core\Contracts\ThemeSetting;
use Juzaweb\Modules\Core\Facades\Chart;
use Juzaweb\Modules\Core\Facades\Field;
use Juzaweb\Modules\Core\Facades\Module;
use Juzaweb\Modules\Core\Facades\PageBlock;
use Juzaweb\Modules\Core\Facades\PageTemplate;
use Juzaweb\Modules\Core\Facades\Sidebar;
use Juzaweb\Modules\Core\Facades\Theme;
use Juzaweb\Modules\Core\Facades\Widget;
use Juzaweb\Modules\Core\Models\User;
use Juzaweb\Modules\Core\Permissions\PermissionServiceProvider;
use Juzaweb\Modules\Core\Providers\CoreServiceProvider;
use Juzaweb\Modules\Core\Translations\TranslationsServiceProvider;
use Juzaweb\Modules\Subscription\Providers\SubscriptionServiceProvider;
use Juzaweb\QueryCache\QueryCacheServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Pion\Laravel\ChunkUpload\Providers\ChunkUploadServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Yajra\DataTables\ButtonsServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\HtmlServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->createDummyTheme();

        parent::setUp();

        $this->createMixManifest();

        // Create class aliases for backward compatibility
        if (! class_exists('Juzaweb\Modules\Admin\Models\User')) {
            class_alias(
                'Juzaweb\Modules\Core\Models\User',
                'Juzaweb\Modules\Admin\Models\User'
            );
        }

        // Load and alias UserFactory
        $factoryPath = __DIR__.'/../database/factories/UserFactory.php';
        if (file_exists($factoryPath)) {
            require_once $factoryPath;
        }

        if (! class_exists('Juzaweb\\Modules\\Admin\\Database\\Factories\\UserFactory')) {
            class_alias(
                'Juzaweb\\Modules\\Core\\Database\\Factories\\UserFactory',
                'Juzaweb\\Modules\\Admin\\Database\\Factories\\UserFactory'
            );
        }

        // Load and alias UserStatus enum
        $enumPath = __DIR__.'/Enums/UserStatus.php';
        if (file_exists($enumPath)) {
            require_once $enumPath;
            if (! enum_exists('Juzaweb\\Modules\\Admin\\Enums\\UserStatus')) {
                class_alias(
                    'Juzaweb\\Modules\\Core\\Tests\\Enums\\UserStatus',
                    'Juzaweb\\Modules\\Admin\\Enums\\UserStatus'
                );
            }
        }

        $this->app[ThemeSetting::class]->set('setup', 1);
    }

    protected function createMixManifest(): void
    {
        $path = public_path('juzaweb');
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents($path.'/mix-manifest.json', json_encode([
            '/js/admin.min.js' => '/js/admin.min.js',
            '/css/admin.min.css' => '/css/admin.min.css',
            '/css/vendor.min.css' => '/css/vendor.min.css',
            '/js/vendor.min.js' => '/js/vendor.min.js',
        ]));
    }

    protected function createDummyTheme(): void
    {
        $path = __DIR__.'/themes/itech';
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (! file_exists($path.'/theme.json')) {
            file_put_contents($path.'/theme.json', json_encode([
                'name' => 'itech',
                'title' => 'Itech Theme',
                'version' => '1.0',
                'require' => [],
            ]));
        }
    }

    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
            QueryCacheServiceProvider::class,
            ActivitylogServiceProvider::class,
            HooksServiceProvider::class,
            TranslationsServiceProvider::class,
            PermissionServiceProvider::class,
            ChunkUploadServiceProvider::class,
            DataTablesServiceProvider::class,
            ButtonsServiceProvider::class,
            HtmlServiceProvider::class,
            SubscriptionServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  Application  $app
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Field' => Field::class,
            'Module' => Module::class,
            'Theme' => Theme::class,
            'Widget' => Widget::class,
            'Sidebar' => Sidebar::class,
            'PageTemplate' => PageTemplate::class,
            'PageBlock' => PageBlock::class,
            'Chart' => Chart::class,
            'DataTables' => DataTables::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('themes.path', __DIR__.'/themes');

        // Use MySQL if DB_CONNECTION is set (e.g., in CI), otherwise use SQLite
        $connection = env('DB_CONNECTION', 'sqlite');

        if ($connection === 'mysql') {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'testing'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);
        } else {
            // Setup default database to use sqlite :memory:
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }

        // Setup filesystem disks for testing
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ]);

        $app['config']->set('filesystems.disks.private', [
            'driver' => 'local',
            'root' => storage_path('app/private'),
        ]);

        $app['config']->set('app.key', 'base64:z6G9E3Z1YgU6b9N8X2S4r0YwR0hK8F8N0d2K1l3b1rA=');
        $app['config']->set('app.locale', 'en');
        $app['config']->set('translatable.fallback_locale', 'en');
        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $connection = config('database.default');

        $this->loadLaravelMigrations(['--database' => $connection]);

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->artisan('migrate', ['--database' => $connection])->run();
    }
}
