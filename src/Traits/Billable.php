<?php

namespace Codestage\Netopia\Traits;

use Carbon\Carbon;
use Codestage\Netopia\Entities\PaymentRequest;
use Codestage\Netopia\Models\{Payment, PaymentMethod};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Netopia\Payment\Address;

/**
 * A trait applied to entities that can be billed through Netopia.
 *
 * @template        TBillable of Illuminate\Database\Eloquent\Model
 * @method          morphMany(string $class, string $string)
 * @property        string|null                     $netopia_token
 * @property        Carbon|null                     $netopia_token_expires_at
 * @property-read   Collection<Payment>             $payments
 * @property-read   Collection<PaymentMethod>       $paymentMethods
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

    /**
     * Get this billable entity's saved payment methods.
     *
     * @return MorphMany
     */
    public function paymentMethods(): MorphMany
    {
        return $this->morphMany(PaymentMethod::class, 'billable');
    }

    /**
     * Interact with the billable entity's token expiration date.
     *
     * @return Attribute
     */
    protected function netopiaTokenExpiresAt(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $value ? Carbon::parse($value) : null,
            set: fn (Carbon|null $value) => $value?->toIso8601String(),
        );
    }
}
