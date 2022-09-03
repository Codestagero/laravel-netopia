<?php

namespace Codestage\Netopia\Contracts;

use Codestage\Netopia\Entities\PaymentRequest;
use Exception;

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
