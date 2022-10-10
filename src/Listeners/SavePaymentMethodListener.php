<?php

namespace Codestage\Netopia\Listeners;

use Codestage\Netopia\Enums\PaymentStatus;
use Codestage\Netopia\Events\PaymentStatusChangedEvent;
use Codestage\Netopia\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Throwable;

class SavePaymentMethodListener
{
    /**
     * Save the payment method on a successful payment with the correct flag.
     *
     * @param PaymentStatusChangedEvent $event
     * @throws Throwable
     * @return void
     */
    public function handle(PaymentStatusChangedEvent $event): void
    {
        if ($event->newStatus === PaymentStatus::Confirmed && $event->oldStatus !== PaymentStatus::Confirmed) {
            Log::debug('Payment status changed', [
                'payment' => $event->payment,
                'result' => $event->result
            ]);

            // If the payment already has a payment method attached, update its token.
            // Otherwise, if this payment's method should be saved, create a payment method.
            if ($event->payment->payment_method_id) {
                if ($event->result->cardMasked) {
                    $event->payment->paymentMethod->masked_number = $event->result->cardMasked;
                }
                if ($event->result->tokenId) {
                    $event->payment->paymentMethod->token_id = $event->result->tokenId;
                }
                if ($event->result->tokenExpiresAt) {
                    $event->payment->paymentMethod->token_expires_at = $event->result->tokenExpiresAt;
                }

                // If the payment method has actually been updated, run the update
                if ($event->payment->paymentMethod->isDirty()) {
                    $event->payment->paymentMethod->save();

                    Log::debug('Payment method updated', [
                        'payment_method' => $event->payment->paymentMethod->getKey(),
                        'payment' => $event->payment->paymentMethod->getKey()
                    ]);
                }
            } else if ($event->payment->payment_method_saved) {
                if ($event->result->cardMasked && $event->result->tokenId && $event->result->tokenExpiresAt) {
                    /** @var PaymentMethod $paymentMethod */
                    $paymentMethod = PaymentMethod::query()->make([
                        'masked_number' => $event->result->cardMasked,
                        'token_id' => $event->result->tokenId,
                        'token_expires_at' => $event->result->tokenExpiresAt,
                    ]);

                    $paymentMethod->billable()->associate($event->payment->billable);

                    $paymentMethod->saveOrFail();

                    Log::debug('Payment method saved', [
                        'payment_method' => $paymentMethod->getKey(),
                        'payment' => $event->payment->getKey()
                    ]);
                } else {
                    Log::warning('Payment method does not have enough info to be saved.', [
                        'event' => $event
                    ]);
                }
            }
        }
    }
}
