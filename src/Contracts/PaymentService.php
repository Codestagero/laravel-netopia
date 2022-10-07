<?php

namespace Codestage\Netopia\Contracts;

use Codestage\Netopia\Entities\{EncryptedPayment, PaymentResult};
use Codestage\Netopia\Exceptions\{ConfigurationException, NetopiaException};
use Codestage\Netopia\Models\Payment;
use Exception;
use SoapFault;

/**
 * @template TBillable
 */
abstract class PaymentService extends NetopiaService
{
    /**
     * Encrypt a payment and prepare it to be sent to Netopia.
     *
     * @param Payment $payment
     * @throws Exception
     * @return EncryptedPayment
     */
    public abstract function generateEncryptedPayment(Payment $payment): EncryptedPayment;

    /**
     * Decrypt a Netopia payment.
     *
     * @param string $environment
     * @param string $data
     * @throws Exception
     * @return PaymentResult
     */
    public abstract function decryptPayment(string $environment, string $data): PaymentResult;

    /**
     * Execute a payment using SOAP.
     *
     * @param Payment $payment
     * @throws SoapFault
     * @throws ConfigurationException
     * @throws NetopiaException
     * @throws Exception
     * @return mixed
     */
    public abstract function executeSoap(Payment $payment): mixed;
}
