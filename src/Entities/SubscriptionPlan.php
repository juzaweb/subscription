<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Entities;

class SubscriptionPlan
{
    public function __construct(protected string $servicePlanId, protected array $data = [])
    {
    }

    public function getServicePlanId(): string
    {
        return $this->servicePlanId;
    }
}
