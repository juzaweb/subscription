<?php

use Juzaweb\Subscription\Http\Controllers\Frontend\PaymentController;

Route::group(
    ['middleware' => 'auth'],
    function () {
        Route::post('subscription/{module}/payment', [PaymentController::class, 'payment'])
            ->name('subscription.module.payment');
        Route::get('subscription/{module}/return/{plan}/{method}', [PaymentController::class, 'return'])
            ->name('subscription.module.return');
        Route::get('subscription/{module}/cancel/{plan}/{method}', [PaymentController::class, 'cancel'])
            ->name('subscription.module.cancel');
    }
);
