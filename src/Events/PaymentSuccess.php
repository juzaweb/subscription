<?php
/**
 * JUZAWEB CMS - The Best CMS for Laravel Project
 *
 * @package    juzaweb/juzacms
 * @author     Juzaweb Team <admin@juzaweb.com>
 * @link       https://juzaweb.com
 * @license    GNU General Public License v2.0
 */

namespace Juzaweb\Subscription\Events;

class PaymentSuccess
{
    public $responre;

    public function __construct($responre)
    {
        $this->responre = $responre;
    }
}
