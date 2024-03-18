<?php

use Foodticket\Takeaway\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/keep-alive/{restaurantId}', [WebhookController::class, 'keepAlive'])
    ->name('takeaway-pos.keep-alive');

Route::any('/{event}', [WebhookController::class, 'handle'])
    ->name('takeaway-pos.event');
