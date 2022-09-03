<?php

namespace Codestage\Netopia\Models;

use Carbon\Carbon;
use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Entities\EncryptedPayment;
use Codestage\Netopia\Enums\PaymentStatus;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property    string          $id
 * @property    PaymentStatus   $status
 * @property    float           $amount
 * @property    string          $currency
 * @property    string|null     $description
 * @property    Carbon          $createdAt
 * @property    Carbon          $updatedAt
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
        'description'
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
     * Get the billable entity that executed this payment.
     *
     * @return MorphTo
     */
    public function billable(): MorphTo
    {
        return $this->morphTo('billable');
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
