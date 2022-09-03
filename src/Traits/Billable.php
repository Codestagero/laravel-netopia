<?php

namespace Codestage\Netopia\Traits;

use Codestage\Netopia\Entities\PaymentRequest;
use Codestage\Netopia\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JetBrains\PhpStorm\ArrayShape;
use Netopia\Payment\Address;

/**
 * @template TBillable of Illuminate\Database\Eloquent\Model
 * @method morphMany(string $class, string $string)
 */
trait Billable
{
    /**
     * Create a new payment for this model.
     *
     * @param array<string, string|float|int|Address> $options
     * @return PaymentRequest<TBillable>
     */
    public function createPayment(
        #[ArrayShape(['description' => 'string', 'amount' => 'float', 'currency' => 'string', 'address' => '\Codestage\Netopia\Entities\Address', 'billingAddress' => '\Codestage\Netopia\Entities\Address', 'shippingAddress' => '\Codestage\Netopia\Entities\Address'])]
        array $options = []
    ): PaymentRequest {
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

        // Fill both address, if provided
        if (isset($options['address'])) {
            $payment->setAddress($options['address']);
        }

        // Fill the address, if provided
        if (isset($options['billingAddress'])) {
            $payment->setBillingAddress($options['billingAddress']);
        }

        // Fill the address, if provided
        if (isset($options['shippingAddress'])) {
            $payment->setShippingAddress($options['shippingAddress']);
        }

        // Return the created payment
        return $payment;
    }

    /**
     * Get this billable entity's payments.
     *
     * @return MorphMany
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'billable');
    }
}
