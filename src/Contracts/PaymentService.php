<?php

namespace Codestage\Netopia\Contracts;

use Codestage\Netopia\Entities\EncryptedPayment;
use Codestage\Netopia\Models\Payment;
use Exception;

/**
 * @template TBillable
 */
abstract class PaymentService extends NetopiaService
{
    /**
     * Execute a Netopia payment.
     *
     * @param Payment $payment
     * @throws Exception
     * @return EncryptedPayment
     */
    public abstract function generateEncryptedPayment(Payment $payment): EncryptedPayment;
}
