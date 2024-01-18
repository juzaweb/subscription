<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Juzaweb\Subscription\Models\Plan;

interface WithSubscriptable
{
    public function moduleSubscription(): HasOne;

    public function moduleSubscriptionEffect();

    public function getSubscriptionPlan(): ?Plan;
}
