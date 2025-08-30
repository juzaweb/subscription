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
    ],
    function () {
        Route::get('{method}/return', [SubscriptionController::class, 'return'])->name('subscription.return');
        Route::get('{method}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    }
);
