<?php

namespace Codestage\Netopia\Enums;

enum PaymentStatus: string
{
    case Confirmed = 'confirmed';
    case Pending = 'pending';
    case Preauthorized = 'preauthorized';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Rejected = 'rejected';
}
