<?php

use Codestage\Netopia\Http\Controllers\{PaymentController, PaymentReturnController};
use Illuminate\Support\Facades\Route;

Route::get('/netopia/pay/{payment}', PaymentController::class)->name('netopia.pay');

Route::get('/netopia/return', [PaymentReturnController::class, 'get'])->name('netopia.return.get');
Route::post('/netopia/return', [PaymentReturnController::class, 'post'])->name('netopia.return');
