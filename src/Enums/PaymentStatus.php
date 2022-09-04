<?php

namespace Codestage\Netopia\Enums;

enum PaymentStatus: string
{
    /**
     * The billable entity has not yet done any action to complete this payment.
     */
    case NotStarted = 'not_started';

    /**
     * This payment was confirmed as successful.
     */
    case Confirmed = 'confirmed';

    /**
     * The billable entity has moved to complete this payment, but it is still pending.
     */
    case Pending = 'pending';

    /**
     * The payment has been pre-authorized.
     */
    case Preauthorized = 'preauthorized';

    /**
     * The payment has been cancelled.
     */
    case Cancelled = 'cancelled';

    /**
     * The payment has been refunded.
     */
    case Refunded = 'refunded';

    /**
     * The payment has been rejected by the processing service.
     */
    case Rejected = 'rejected';
}
