<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Services;

use Juzaweb\Modules\Subscription\Contracts\SubscriptionModule;
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;

class TestSubscription implements SubscriptionModule
{
    protected string $name = 'Membership';

    public function onPaymentSuccess(SubscriptionReturnResult $result)
    {
        dd('Payment success');
    }

    public function onPaymentCancel(SubscriptionReturnResult $result)
    {
        // Handle payment cancellation
    }

    public function getName(): string
    {
        return $this->name;
    }
}
