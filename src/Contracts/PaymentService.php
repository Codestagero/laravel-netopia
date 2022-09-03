<?php

namespace iRealWorlds\Netopia\Contracts;

use Exception;
use iRealWorlds\Netopia\Entities\PaymentRequest;

/**
 * @template TBillable
 */
abstract class PaymentService extends NetopiaService
{
    /**
     * Execute a Netopia payment.
     *
     * @param PaymentRequest<TBillable> $payment
     * @throws Exception
     * @return string
     */
    public abstract function generatePaymentUri(PaymentRequest $payment): string;
}
