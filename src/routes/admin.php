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

Route::jwResource('subscription/{module}/plans', PaymentMethodController::class);
Route::jwResource('subscription/{module}/payment-methods', PaymentMethodController::class);
