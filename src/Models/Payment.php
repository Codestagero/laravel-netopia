<?php

namespace Codestage\Netopia\Models;

use Carbon\Carbon;
use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Entities\{Address, EncryptedPayment, PaymentMetadataItem};
use Codestage\Netopia\Enums\PaymentStatus;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property-read   string                      $id
 * @property        PaymentStatus               $status
 * @property        float                       $amount
 * @property        string                      $currency
 * @property        string|null                 $description
 * @property        Address|null                $shipping_address
 * @property        Address|null                $billing_address
 * @property        PaymentMetadataItem[]       $metadata
 * @property        bool                        $payment_method_saved
 * @property        int                         $payment_method_id
 * @property        Carbon                      $createdAt
 * @property        Carbon                      $updatedAt
 * @property-read   Model                       $billable
 * @property-read   PaymentMethod               $paymentMethod
 */
class Payment extends Model
{
    /**
     * @inheritDoc
     */
    protected $keyType = 'string';

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $table = 'netopia_payments';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'status',
        'amount',
        'currency',
        'description',
        'shipping_address',
        'billing_address',
        'metadata',
        'payment_method_saved',
        'payment_method_id',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'status' => PaymentStatus::class
    ];

    /**
     * @inheritDoc
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $model): void {
            // Generate a payment id
            $model->id = 'payment_' . Str::uuid();
        });

        static::saving(function (Payment $model): void {
            // Prevent changing the payment id
            $originalPaymentId = $model->getOriginal('id');

            if ($originalPaymentId !== $model->id) {
                $model->id = $originalPaymentId;
            }
        });
    }

    /**
     * Interact with the payment's billing address.
     *
     * @return Attribute
     */
    protected function billingAddress(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): Address|null => Address::fromJson($value),
            set: fn (Address|null $value): string => $value?->toJson() ?? 'null',
        );
    }

    /**
     * Interact with the payment's shipping address.
     *
     * @return Attribute
     */
    protected function shippingAddress(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): Address|null => Address::fromJson($value),
            set: fn (Address|null $value): string => $value?->toJson() ?? 'null',
        );
    }

    /**
     * Interact with the payment's metadata.
     *
     * @return Attribute
     */
    protected function metadata(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): array => array_map(fn (array $item) => new PaymentMetadataItem($item['key'], $item['value']), json_decode($value, true)),
            set: fn (array $value): string => json_encode($value),
        );
    }

    /**
     * Get the billable entity that executed this payment.
     *
     * @return MorphTo
     */
    public function billable(): MorphTo
    {
        return $this->morphTo('billable');
    }

    /**
     * Get the payment method used for this payment.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Wrapper for the PaymentService::generateEncryptedPayment method.
     *
     * @throws Exception
     * @return EncryptedPayment
     */
    public function encrypt(): EncryptedPayment
    {
        /** @var PaymentService $paymentService */
        $paymentService = App::make(PaymentService::class);

        return $paymentService->generateEncryptedPayment($this);
    }
}
