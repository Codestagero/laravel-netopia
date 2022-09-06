<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Models\Payment;
use Illuminate\Support\Facades\{Config};
use Throwable;

/**
 * @template TBillable of Illuminate\Database\Eloquent\Model
 */
class PaymentRequest
{
    /**
     * The amount of this payment.
     *
     * @var float
     */
    public float $amount;

    /**
     * The currency the amount is in.
     *
     * @var string
     */
    public string $currency;

    /**
     * This payment's description.
     *
     * @var string
     */
    public string $description = '';

    /**
     * The billable entity that is performing this payment.
     *
     * @var TBillable
     */
    public mixed $billable;

    /**
     * The address assigned to this payment.
     *
     * @var Address|null
     */
    public Address|null $billingAddress = null;

    /**
     * The shipping assigned to this payment.
     *
     * @var Address|null
     */
    public Address|null $shippingAddress = null;

    /**
     * The metadata assigned to this payment.
     *
     * @var PaymentMetadataItem[]
     */
    public array $metadata = [];

    /**
     * Payment constructor method.
     *
     * @param TBillable $billable
     */
    public function __construct(mixed $billable)
    {
        $this->billable = $billable;
        $this->currency = Config::get('netopia.currency');
    }

    /**
     * Set this payment's requested amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the currency used for this payment.
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Set the currency used for this payment.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set both the shipping and billing addresses used for this payment.
     *
     * @param Address|null $address
     * @return $this
     */
    public function setAddress(Address|null $address): static
    {
        $this->shippingAddress = $address;
        $this->billingAddress = $address;

        return $this;
    }

    /**
     * Set the address used for this payment.
     *
     * @param Address|null $billingAddress
     * @return $this
     */
    public function setBillingAddress(Address|null $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Set the address used for this payment.
     *
     * @param Address|null $billingAddress
     * @return $this
     */
    public function setShippingAddress(Address|null $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Set the metadata used for this payment.
     *
     * @param PaymentMetadataItem[] $metadata
     * @return $this
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Add an item to the metadata used for this payment.
     *
     * @param PaymentMetadataItem $metadata
     * @return $this
     */
    public function addMetadata(PaymentMetadataItem $metadata): static
    {
        $this->metadata[] = $metadata;

        return $this;
    }

    /**
     * Commit this payment to the database.
     *
     * @throws Throwable
     * @return Payment
     */
    public function save(): Payment
    {
        /** @var Payment $payment */
        $payment = Payment::query()->make([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
        ]);
        $payment->billable()->associate($this->billable);
        $payment->billing_address = $this->billingAddress;
        $payment->shipping_address = $this->shippingAddress;
        $payment->metadata = $this->metadata;
        $payment->saveOrFail();

        return $payment;
    }
}
