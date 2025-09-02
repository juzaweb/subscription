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

use Illuminate\Support\Facades\Facade;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;

/**
 * @method static \Illuminate\Support\Collection<SubscriptionMethod> drivers()
 * @method static string renderConfig(string $driver, array $config = []): string
 * @method static SubscriptionMethod driver(string $name)
 * @method static \Juzaweb\Modules\Subscription\Entities\SubscribeResult create($user, string $module, ?\Juzaweb\Modules\Subscription\Models\Plan $plan, \Juzaweb\Modules\Subscription\Models\SubscriptionMethod $method, array $options = [])
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
