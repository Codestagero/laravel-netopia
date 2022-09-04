<?php

use Codestage\Netopia\Http\Controllers\{PaymentController, PaymentReturnController};
use Illuminate\Support\Facades\Route;

Route::get('/netopia/pay/{payment}', PaymentController::class)->name('netopia.pay');

Route::post('/netopia/ipn', [PaymentReturnController::class, 'post'])->name('netopia.ipn');
