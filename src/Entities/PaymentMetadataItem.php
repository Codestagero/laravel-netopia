<?php

namespace Codestage\Netopia\Entities;

/**
 * @template TValue
 */
class PaymentMetadataItem
{
    /**
     * PaymentMetadataItem constructor method.
     *
     * @param string $key
     * @param TValue $value
     */
    public function __construct(public string $key, public mixed $value)
    {
    }
}
