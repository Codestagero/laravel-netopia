<?php

namespace Codestage\Netopia\Models;

use Carbon\Carbon;
use Codestage\Netopia\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * @property    string          $id
 * @property    PaymentStatus   $status
 * @property    Carbon          $createdAt
 * @property    Carbon          $updatedAt
 */
class Payment extends Model
{
    /**
     * @inheritDoc
     */
    protected $table = 'netopia_payments';

    /**
     * @inheritDoc
     */
    protected $casts = [
        'status' => PaymentStatus::class
    ];
}
