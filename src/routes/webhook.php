<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

use Juzaweb\Modules\Subscription\Http\Controllers\SubscriptionController;

Route::get('subscription/{method}/webhook', [SubscriptionController::class, 'webhook'])
    ->name('subscription.webhook');
