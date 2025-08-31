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

class TestSubscription implements SubscriptionModule
{
    protected string $name = 'Test';

    protected string $serviceName = 'Test Service';

    public function onSuccess(SubscriptionResult $result, array $params = [])
    {
        dd('Payment success');
    }

    public function onCancel(SubscriptionResult $result, array $params = [])
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
}
