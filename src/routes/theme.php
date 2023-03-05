<?php

use Juzaweb\Subscription\Http\Controllers\Frontend\PaymentController;

Route::post('subscription/{module}/payment/{plan}/{method}', [PaymentController::class, 'payment'])
    ->name('subscription.module.payment');
Route::get('subscription/{module}/return/{plan}/{method}', [PaymentController::class, 'return'])
    ->name('subscription.module.return');
