<?php

use Codestage\Netopia\Http\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/netopia/pay/{payment}', PaymentController::class);
