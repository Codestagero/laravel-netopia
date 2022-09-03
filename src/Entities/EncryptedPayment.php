<?php

namespace Codestage\Netopia\Entities;

class EncryptedPayment
{
    /**
     * EncryptedPayment constructor method.
     *
     * @param string $url
     * @param string $environmentKey
     * @param string $data
     */
    public function __construct(
        public readonly string $url,
        public readonly string $environmentKey,
        public readonly string $data
    ) {
    }
}
