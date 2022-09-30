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

            // If the payment already has a payment method attached, update its token.
            // Otherwise, if this payment's method should be saved, create a payment method.
            if ($event->payment->payment_method_id) {
                $event->payment->paymentMethod->update([
                    'masked_number' => $event->result->cardMasked,
                    'token_id' => $event->result->tokenId,
                    'token_expires_at' => $event->result->tokenExpiresAt,
                ]);

                Log::debug('Payment method updated', [
                    'payment_method' => $event->payment->paymentMethod->getKey(),
                    'payment' => $event->payment->paymentMethod->getKey()
                ]);
            } else if ($event->payment->payment_method_saved) {
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
                    'payment' => $paymentMethod->getKey()
                ]);
            }
        }
    }
}
