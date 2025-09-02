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
use Juzaweb\Modules\Subscription\Entities\SubscriptionResult;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;

class TestSubscription implements SubscriptionModule
{
    protected string $name = 'Test';

    protected string $serviceName = 'Test Service';

    public function onSuccess(SubscriptionResult $result, array $params = [])
    {
        info('Payment success', [
            'subscription_history_id' => $result->getSubscriptionHistory()->id,
            'params' => $params,
        ]);
    }

    public function onCancel(SubscriptionHistory $result, array $params = [])
    {
        // Handle payment cancellation
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getReturnUrl()
    {
        return admin_url('subscription-methods');
    }
}
