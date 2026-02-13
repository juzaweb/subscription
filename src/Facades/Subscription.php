<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Juzaweb\Modules\Subscription\Contracts\Subscriptable;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionModule;
use Juzaweb\Modules\Subscription\Entities\Feature;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Models\Plan;

/**
 * @method static Collection<SubscriptionMethod> drivers()
 * @method static string renderConfig(string $driver, array $config = []): string
 * @method static SubscriptionMethod driver(string $name)
 * @method static SubscribeResult create($user, Subscriptable $subscriptable, string $module, ?Plan $plan, \Juzaweb\Modules\Subscription\Models\SubscriptionMethod $method, array $options = [])
 * @method static webhook(Request $request, string $driver)
 * @method static void registerDriver(string $name, callable $resolver)
 * @method static void registerModule(string $name, callable $resolver)
 * @method static Collection modules()
 * @method static SubscriptionModule module(string $name)
 * @method static boolean hasModule(string $module)
 * @method static void feature(string $key, string $module, callable $callback)
 * @method static Collection<string, Feature> features(string $module)
 * @see \Juzaweb\Modules\Subscription\Services\SubscriptionManager
 */
class Subscription extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Juzaweb\Modules\Subscription\Contracts\Subscription::class;
    }
}
