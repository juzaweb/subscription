<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Events;

use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\UserSubscription;

class WebhookHandleSuccess
{
    public function __construct(
        protected PaymentReturnResult $agreement,
        protected PaymentMethod $method,
        protected UserSubscription $subscriber,
        protected ?PaymentHistory $paymentHistory
    ) {
        //
    }
}
