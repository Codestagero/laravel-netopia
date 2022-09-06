<?php

namespace Codestage\Netopia\Entities;

use Carbon\Carbon;
use Codestage\Netopia\Enums\{ExceptionCode, PaymentStatus};
use Netopia\Payment\Request\PaymentAbstract;

class PaymentResult
{
    /**
     * The updated payment status.
     *
     * @var PaymentStatus|null
     */
    public PaymentStatus|null $newStatus = null;

    /**
     * The type of error that resulted.
     *
     * @var int
     */
    public int $errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_NONE;

    /**
     * The resulting error's code.
     *
     * @var ExceptionCode
     */
    public ExceptionCode $errorCode = ExceptionCode::Approved;

    /**
     * The text body of the error that resulted.
     *
     * @var string
     */
    public string $errorText = '';

    /**
     * The Id of the payment this result is associated to.
     *
     * @var string|null
     */
    public string|null $paymentId = null;

    /**
     * The transaction reference number for this payment.
     *
     * @var string|null
     */
    public string|null $transactionReference = null;

    /**
     * The billable entity token received in this result.
     *
     * @var string|null
     */
    public string|null $tokenId = null;

    /**
     * The expiration date of the billable token received in this result.
     *
     * @var Carbon|null
     */
    public Carbon|null $tokenExpiresAt = null;

    /**
     * PaymentResult constructor method.
     *
     * @param array $initial
     */
    public function __construct(array $initial = [])
    {
        if (isset($initial['newStatus'])) {
            $this->newStatus = $initial['newStatus'];
        }

        if (isset($initial['errorCode'])) {
            if (is_numeric($initial['errorCode'])) {
                $this->errorCode = ExceptionCode::tryFrom($initial['errorCode']) ?? ExceptionCode::Approved;
            } else if ($initial['errorCode'] instanceof ExceptionCode) {
                $this->errorCode = $initial['errorCode'];
            }
        }

        if (isset($initial['paymentId'])) {
            $this->paymentId = $initial['paymentId'];
        }

        if (isset($initial['transactionReference'])) {
            $this->transactionReference = $initial['transactionReference'];
        }

        if (isset($initial['errorText'])) {
            $this->errorText = $initial['errorText'];
        }

        if (isset($initial['errorType'])) {
            $this->errorType = $initial['errorType'];
        }

        if (isset($initial['tokenId'])) {
            $this->tokenId = $initial['tokenId'];
        }

        if (isset($initial['tokenExpiresAt'])) {
            if ($initial['tokenExpiresAt'] instanceof Carbon) {
                $this->tokenExpiresAt = $initial['tokenExpiresAt'];
            } else {
                $this->tokenExpiresAt = Carbon::parse($initial['tokenExpiresAt']);
            }
        }
    }
}
