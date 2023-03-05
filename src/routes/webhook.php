<?php

use Juzaweb\Subscription\Http\Controllers\Frontend\PaymentController;

Route::match(['GET', 'POST'], 'subscription/{module}/{method}', [PaymentController::class, 'webhook'])
    ->name('subscription.module');
