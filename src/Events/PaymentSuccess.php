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

use Juzaweb\Subscription\Contrasts\PaymentResult;
use Juzaweb\Subscription\Models\PaymentHistory;

class PaymentSuccess
{
    public function __construct(
        protected PaymentResult $paymentReturnResult,
        PaymentHistory $paymentHistory
    ) {
    }
}
