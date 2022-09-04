<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Enums\PaymentStatus;
use Netopia\Payment\Request\PaymentAbstract;

class PaymentResult
{
    public PaymentStatus $newStatus;

    public int $errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_NONE;
    public int $errorCode = 0;
    public string $errorText = '';

    /**
     * PaymentResult constructor method.
     *
     * @param PaymentStatus $newStatus
     * @param int $errorCode
     * @param int $errorType
     * @param string $errorText
     */
    public function __construct(PaymentStatus $newStatus, int $errorCode = 0, int $errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_NONE, string $errorText = '')
    {
        $this->newStatus = $newStatus;
        $this->errorCode = $errorCode;
        $this->errorText = $errorText;
        $this->errorType = $errorType;
    }
}
