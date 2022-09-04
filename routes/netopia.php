<?php

use Codestage\Netopia\Http\Controllers\{PaymentController, PaymentReturnController};
use Illuminate\Support\Facades\Route;

Route::get('/netopia/pay/{payment}', PaymentController::class)->name('netopia.pay');

Route::get('/netopia/success', [PaymentReturnController::class, 'success'])->name('netopia.success');
Route::post('/netopia/ipn', [PaymentReturnController::class, 'ipn'])->name('netopia.ipn');
