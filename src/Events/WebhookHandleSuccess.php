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

use Juzaweb\Subscription\Contrasts\PaymentResult;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;

class WebhookHandleSuccess
{
    public function __construct(
        protected PaymentResult $agreement,
        protected PaymentMethod $method,
        protected ?PaymentHistory $paymentHistory
    ) {
        //
    }
}
