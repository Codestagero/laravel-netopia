<?php

namespace iRealWorlds\Netopia\Traits;

use iRealWorlds\Netopia\Entities\PaymentRequest;
use Netopia\Payment\Address;

/**
 * @template TBillable of Illuminate\Database\Eloquent\Model
 */
trait Billable
{
    use HasAddress;

    /**
     * Create a new payment for this model.
     *
     * @param array<string, string|float|int|Address> $options
     * @return PaymentRequest<TBillable>
     */
    public function createPayment(array $options = []): PaymentRequest
    {
        // Create a new payment entity
        $payment = new PaymentRequest($this);

        // Fill the description, if provided
        if (isset($options['description'])) {
            $payment->setDescription($options['description']);
        }

        // Fill the amount, if provided
        if (isset($options['amount'])) {
            $payment->setAmount($options['amount']);
        }

        // Fill the currency, if provided
        if (isset($options['currency'])) {
            $payment->setCurrency($options['currency']);
        }

        // Fill the address, if provided
        if (isset($options['address'])) {
            $payment->setAddress($options['address']);
        }

        // Return the created payment
        return $payment;
    }
}
