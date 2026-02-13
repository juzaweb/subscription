<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

use Juzaweb\Modules\Core\Facades\RouteResource;
use Juzaweb\Modules\Subscription\Http\Controllers\PlanController;
use Juzaweb\Modules\Subscription\Http\Controllers\SubscriptionController;
use Juzaweb\Modules\Subscription\Http\Controllers\SubscriptionMethodController;
use Juzaweb\Modules\Subscription\Http\Middleware\ModuleValid;

RouteResource::admin('subscription-methods', SubscriptionMethodController::class);
Route::get('subscription-methods/{driver}/get-data', [SubscriptionMethodController::class, 'getData'])
    ->name('subscription-methods.data');
Route::post('subscription-methods/update-sandbox', [SubscriptionMethodController::class, 'updateSandbox'])
    ->name('subscription-methods.update-sandbox');

Route::group(
    ['middleware' => [ModuleValid::class]],
    function () {
        RouteResource::admin('subscription/{module}/plans', PlanController::class);
        Route::get('subscription/{module}/subscriptions', [SubscriptionController::class, 'index']);
    }
);
