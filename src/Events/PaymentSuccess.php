<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/cms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    GNU General Public License v2.0
 */

namespace Juzaweb\Subscription\Events;

use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\UserSubscription;

class PaymentSuccess
{
    public function __construct(
        protected PaymentReturnResult $paymentReturnResult,
        UserSubscription $userSubscription,
        PaymentHistory $paymentHistory
    ) {
    }
}
