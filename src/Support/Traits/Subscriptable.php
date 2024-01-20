<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Support\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\Plan;

/**
 * @property-read ModuleSubscription|null $moduleSubscription
 * @property-read ModuleSubscription|null $moduleSubscriptionEffect
 */
trait Subscriptable
{
    public function moduleSubscription(): HasOne
    {
        return $this->hasOne(ModuleSubscription::class, 'module_id', 'id')
            ->where('subscription_module_subscriptions.module_type', $this->subscriptionModule);
    }

    public function moduleSubscriptionEffect()
    {
        return $this->moduleSubscription()->inEffect();
    }

    public function getSubscriptionPlan(): ?Plan
    {
        return $this->moduleSubscriptionEffect?->plan;
    }
}
