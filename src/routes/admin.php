<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

use Juzaweb\Core\Facades\RouteResource;
use Juzaweb\Modules\Subscription\Http\Controllers\SubscriptionMethodController;

RouteResource::admin('subscription-methods', SubscriptionMethodController::class);
Route::get('subscription-methods/{driver}/get-data', [SubscriptionMethodController::class, 'getData'])
    ->name('subscription-methods.data');
