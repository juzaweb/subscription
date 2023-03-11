<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

use Juzaweb\Subscription\Http\Controllers\Backend\PaymentMethodController;
use Juzaweb\Subscription\Http\Controllers\Backend\PlanController;
use Juzaweb\Subscription\Http\Controllers\Backend\UserSubscriptionController;

Route::jwResource('subscription/{module}/plans', PlanController::class);
Route::jwResource('subscription/{module}/payment-methods', PaymentMethodController::class);
Route::jwResource('subscription/{module}/subscriptions', UserSubscriptionController::class);
Route::jwResource('subscription/{module}/payment-histories', PaymentMethodController::class);
