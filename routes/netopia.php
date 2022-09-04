<?php

use Codestage\Netopia\Http\Controllers\{PaymentController, PaymentReturnController};
use Illuminate\Support\Facades\Route;

Route::get('/netopia/pay/{payment}', PaymentController::class)->name('netopia.pay');
Route::post('/netopia/return', PaymentReturnController::class)->name('netopia.return');
