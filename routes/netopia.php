<?php

use Codestage\Netopia\Http\Controllers\{PaymentController, PaymentReturnController};
use Illuminate\Support\Facades\Route;

$routing = Route::prefix(config('netopia.route_prefix'))
    ->middleware(config('netopia.route_middleware', 'web'))
    ->name('netopia.');

if (config('netopia.domain')) {
    $routing = $routing->domain(config('netopia.domain'));
}

$routing->group(function (): void {
    Route::get('{payment}', PaymentController::class)->name('pay');

    Route::get('success', [PaymentReturnController::class, 'success'])->name('success');
    Route::post('ipn', [PaymentReturnController::class, 'ipn'])->name('ipn');
});
