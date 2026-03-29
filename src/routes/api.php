<?php

/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @author     The Anh Dang
 *
 * @link       https://cms.juzaweb.com
 *
 * @license    GNU V2
 */

use Illuminate\Support\Facades\Route;
use Juzaweb\Modules\Subscription\Http\Controllers\API\PlanController;
use Juzaweb\Modules\Subscription\Http\Controllers\API\SubscriptionController;
use Juzaweb\Modules\Subscription\Http\Controllers\API\SubscriptionHistoryController;
use Juzaweb\Modules\Subscription\Http\Controllers\API\SubscriptionMethodController;

Route::get('subscription/methods', [SubscriptionMethodController::class, 'index']);
Route::get('subscription/plans', [PlanController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('subscription/subscriptions', [SubscriptionController::class, 'index']);
    Route::get('subscription/histories', [SubscriptionHistoryController::class, 'index']);

    Route::post('subscription/{module}/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('subscription/{module}/return/{transactionId}', [SubscriptionController::class, 'return']);
    Route::get('subscription/{module}/cancel/{transactionId}', [SubscriptionController::class, 'cancel']);
});
