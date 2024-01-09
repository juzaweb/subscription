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

class PaymentReturn
{
    public function __construct(
        public PaymentResult $paymentReturnResult,
        public PaymentHistory $paymentHistory
    ) {
    }
}
