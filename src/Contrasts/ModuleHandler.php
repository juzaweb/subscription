<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Contrasts;

interface ModuleHandler
{
    public function onReturn(PaymentResult $result): void;

    public function onWebhook(PaymentResult $result): void;
}
