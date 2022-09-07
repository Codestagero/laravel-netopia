<?php

namespace Codestage\Netopia;

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
            if ($event->payment->payment_method_saved) {
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
