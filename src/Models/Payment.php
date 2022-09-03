<?php

namespace iRealWorlds\Netopia\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use iRealWorlds\Netopia\Enums\PaymentStatus;

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
