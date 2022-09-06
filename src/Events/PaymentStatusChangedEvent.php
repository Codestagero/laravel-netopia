<?php

namespace Codestage\Netopia\Events;

use Codestage\Netopia\Enums\PaymentStatus;
use Codestage\Netopia\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentStatusChangedEvent
{
    use Dispatchable;

    /**
     * PaymentStatusChangedEvent constructor method.
     *
     * @param Payment $payment
     * @param PaymentStatus $oldStatus
     * @param PaymentStatus $newStatus
     */
    public function __construct(
        public readonly Payment $payment,
        public readonly PaymentStatus $oldStatus,
        public readonly PaymentStatus $newStatus
    ) {
    }
}
