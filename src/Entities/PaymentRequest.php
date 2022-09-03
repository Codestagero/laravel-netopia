<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Exceptions\PaymentAlreadyExecutedException;
use Exception;
use Illuminate\Support\Facades\{App, Config};
use Illuminate\Support\Str;
use Netopia\Payment\Address;

/**
 * @template TBillable of Illuminate\Database\Eloquent\Model
 */
class PaymentRequest
{
    /**
     * This payment's generated id.
     *
     * @var string
     */
    public readonly string $id;

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
     * The Netopia address used for this payment.
     *
     * @var Address
     */
    public Address $address;

    /**
     * Whether this payment has already been executed.
     *
     * @var bool
     */
    private bool $wasExecuted = false;

    /**
     * Payment constructor method.
     *
     * @param TBillable $billable
     */
    public function __construct(mixed $billable)
    {
        $this->billable = $billable;

        if (method_exists($billable, 'netopiaAddress')) {
            $this->address = $billable->netopiaAddress();
        }

        $this->currency = Config::get('netopia.currency');
        $this->id = 'payment_' . Str::uuid();
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
     * Set the address used for this payment.
     *
     * @param Address $address
     * @return $this
     */
    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Execute this payment as a bank transfer.
     *
     * @throws Exception
     * @return string
     */
    public function generateUri(): string
    {
        // If this payment has already been executed, throw an exception
        if ($this->wasExecuted) {
            throw new PaymentAlreadyExecutedException();
        }

        // Execute this payment
        /** @var PaymentService<TBillable> $bankTransferService */
        $bankTransferService = App::make(PaymentService::class);
        $result = $bankTransferService->generatePaymentUri($this);

        // Mark that this payment has already been executed.
        $this->wasExecuted = true;

        // Return the payment's result
        return $result;
    }
}
