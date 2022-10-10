<?php

namespace Codestage\Netopia\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * The details of the payment method used for completing a given payment.
 *
 * @note This differs from the PaymentMethod entity in that it is inteded to be used for archiving, not as a reusable entity.
 * @property int            $id
 * @property mixed          $payment_id
 * @property string|null    $masked_number
 * @property string|null    $token_id
 * @property Carbon|null    $token_expires_at
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 */
class PaymentCard extends Model
{
    /** @inheritDoc */
    protected $table = 'netopia_payment_cards';

    /** @inheritDoc */
    protected $fillable = [
        'payment_id',
        'masked_number',
        'token_id',
        'token_expires_at',
    ];
}
