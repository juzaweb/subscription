<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Contracts;

use Juzaweb\Modules\Subscription\Entities\SubscriptionResult;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;

interface SubscriptionModule
{
    public function onSuccess(SubscriptionResult $result, array $params = []);

    public function onCancel(SubscriptionHistory $result, array $params = []);

    public function getName(): string;

    public function getServiceName(): string;
}
