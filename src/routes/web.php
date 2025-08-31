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

Route::group(
    [
        'prefix' => 'subscription',
        'middleware' => [
            ...config('core.auth_middleware')
        ],
    ],
    function () {
        Route::post('{module}/subscribe', [SubscriptionController::class, 'subscribe'])
            ->name('subscription.subscribe');
        Route::get('{module}/return/{transactionId}', [SubscriptionController::class, 'return'])
            ->name('subscription.return');
        Route::get('{module}/cancel/{transactionId}', [SubscriptionController::class, 'cancel'])
            ->name('subscription.cancel');
    }
);
