<?php

namespace Codestage\Netopia\Listeners;

use Codestage\Netopia\Enums\PaymentStatus;
use Codestage\Netopia\Events\PaymentStatusChangedEvent;
use Codestage\Netopia\Traits\Billable;
use Illuminate\Database\Eloquent\Model;

class UpdateBillableTokenOnPayment
{
    /**
     * If a token is received with this payment, store it in the billable model.
     *
     * @param PaymentStatusChangedEvent $event
     * @return void
     */
    public function handle(PaymentStatusChangedEvent $event): void
    {
        if ($event->newStatus === PaymentStatus::Confirmed && $event->oldStatus !== PaymentStatus::Confirmed) {
            if ($event->result->tokenId) {
                /** @var Model|Billable $billable */
                $billable = $event->payment->billable;
                $billable->netopia_token = $event->result->tokenId;
                $billable->netopia_token_expires_at = $event->result->tokenExpiresAt;

                $billable->save();
            }
        }
    }
}
