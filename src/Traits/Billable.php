<?php

namespace Codestage\Netopia\Traits;

use Codestage\Netopia\Entities\PaymentRequest;
use Codestage\Netopia\Models\Payment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Netopia\Payment\Address;

/**
 * @template TBillable of Illuminate\Database\Eloquent\Model
 * @method morphMany(string $class, string $string)
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
