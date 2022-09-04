<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Enums\PaymentStatus;

class PaymentResult
{
    public PaymentStatus $newStatus;

    /**
     * PaymentResult constructor method.
     *
     * @param PaymentStatus $newStatus
     */
    public function __construct(PaymentStatus $newStatus)
    {
        $this->newStatus = $newStatus;
    }
}
