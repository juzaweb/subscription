<?php

use Juzaweb\CMS\Models\User;
use Juzaweb\Subscription\Models\UserSubscription;

if (!function_exists('has_subscription')) {
    function has_subscription(User $user): UserSubscription
    {
        return UserSubscription::whereUserId($user->id)->isActive()->first();
    }
}
