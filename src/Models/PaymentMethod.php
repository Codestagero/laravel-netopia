<?php

namespace Codestage\Netopia\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @property-read       string      $id
 * @property            string      $masked_number
 * @property            string      $token_id
 * @property            string      $billable_type
 * @property            int         $billable_id
 * @property            Carbon      $token_expires_at
 * @property            Carbon      $created_at
 * @property            Carbon      $updated_at
 * @property-read       Model       $billable
 */
class PaymentMethod extends Model
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
    protected $table = 'netopia_payment_methods';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'masked_number',
        'token_id',
        'token_expires_at',
        'billable_id',
        'billable_type'
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'token_expires_at' => 'datetime'
    ];

    /**
     * @inheritDoc
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PaymentMethod $model): void {
            // Generate a payment id
            $model->id = 'pm_' . Str::uuid();
        });

        static::saving(function (PaymentMethod $model): void {
            // Prevent changing the payment id
            $originalPaymentId = $model->getOriginal('id');

            if ($originalPaymentId !== $model->id) {
                $model->id = $originalPaymentId;
            }
        });
    }

    /**
     * Get the billable entity this payment method belongs to.
     *
     * @return MorphTo
     */
    public function billable(): MorphTo
    {
        return $this->morphTo('billable');
    }
}
