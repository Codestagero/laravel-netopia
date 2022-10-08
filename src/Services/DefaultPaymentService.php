<?php

namespace Codestage\Netopia\Services;

use Carbon\Carbon;
use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Entities\{Address, EncryptedPayment, PaymentResult};
use Codestage\Netopia\Enums\PaymentStatus;
use Codestage\Netopia\Exceptions\{ConfigurationException, NetopiaException};
use Codestage\Netopia\Models\Payment;
use Codestage\Netopia\Traits\Billable;
use Exception;
use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Log\LogManager;
use Netopia\Payment\Invoice;
use Netopia\Payment\Request\{Card, PaymentAbstract};
use SoapClient;
use SoapFault;
use stdClass;
use function in_array;
use const WSDL_CACHE_NONE;

/**
 * @template TBillable
 * @extends PaymentService<TBillable>
 */
class DefaultPaymentService extends PaymentService
{
    /**
     * @inheritDoc
     * @param ConfigurationRepository $_configuration
     * @param LogManager $_logManager
     */
    public function __construct(
        private readonly ConfigurationRepository $_configuration,
        private readonly LogManager $_logManager,
        private readonly UrlGenerator $_urlGenerator
    ) {
        parent::__construct($this->_configuration);
    }

    /**
     * Execute a Netopia payment.
     *
     * @param Payment $payment
     * @throws Exception
     * @return EncryptedPayment
     */
    public function generateEncryptedPayment(Payment $payment): EncryptedPayment
    {
        $paymentRequest = new Card();
        $paymentRequest->signature = $this->_configuration->get('netopia.signature'); //signature - generated by mobilpay.ro for every merchant account
        $paymentRequest->orderId = $payment->getKey(); // order_id should be unique for a merchant account
        $paymentRequest->confirmUrl = $this->_urlGenerator->route('netopia.ipn'); // is where mobilPay redirects the client once the payment process is finished and is MANDATORY
        $paymentRequest->returnUrl = $this->_urlGenerator->route('netopia.success'); // is where mobilPay will send the payment result and is MANDATORY
        $paymentRequest->type = PaymentAbstract::PAYMENT_TYPE_CARD;

        // Invoices info
        $paymentRequest->invoice = new Invoice();
        $paymentRequest->invoice->currency = $payment->currency;
        $paymentRequest->invoice->amount = (string) $payment->amount;
        $paymentRequest->invoice->tokenId = $this->extractPaymentBillableToken($payment);
        $paymentRequest->invoice->details = $payment->description;

        if ($payment->billing_address instanceof Address) {
            // Billing Info
            $paymentRequest->invoice->setBillingAddress($payment->billing_address->toNetopia());
        }

        if ($payment->shipping_address instanceof Address) {
            // Shipping Info
            $paymentRequest->invoice->setShippingAddress($payment->shipping_address->toNetopia());
        }

        $this->_logManager->debug('Encrypted payment', [
            $paymentRequest
        ]);

        // encrypting
        $paymentRequest->encrypt($this->certificatePath);

        /**
         * Send the following data to NETOPIA Payments server
         * Method : POST
         * URL : $paymentUrl.
         */
        $envKey = $paymentRequest->getEnvKey();
        $data = $paymentRequest->getEncData();

        return new EncryptedPayment($this->baseUrl, $envKey, $data, );
    }

    /**
     * Decrypt a Netopia payment.
     *
     * @param string $environment
     * @param string $data
     * @throws Exception
     * @return PaymentResult
     */
    public function decryptPayment(string $environment, string $data): PaymentResult
    {
        // Decrypt the payment result data
        $paymentData = Card::factoryFromEncrypted($environment, $data, $this->secretKeyPath);

        // Determine the new payment status
        if ((int) $paymentData->objPmNotify->errorCode === 0) {
            $status = match ($paymentData->objPmNotify->action) {
                'confirmed' => PaymentStatus::Confirmed,
                'paid_pending', 'confirmed_pending' => PaymentStatus::Pending,
                'paid' => PaymentStatus::Preauthorized,
                'canceled' => PaymentStatus::Cancelled,
                'credit' => PaymentStatus::Refunded,
            };
        } else {
            $status = PaymentStatus::Rejected;
        }

        $this->_logManager->debug('Decrypted payment', [
            'payment' => $paymentData,
            'notify' => $paymentData->objPmNotify,
            'errorCode' => $paymentData->objPmNotify->errorCode,
            'action' => $paymentData->objPmNotify->action,
            'status' => $status,
        ]);

        // Build and return the result
        return new PaymentResult([
            'newStatus' => $status,
            'paymentId' => $paymentData->orderId,
            'transactionReference' => $paymentData->objPmNotify->rrn,
            'errorCode' => $paymentData->objPmNotify->errorCode,
            'errorText' => $paymentData->objPmNotify->errorMessage,
            'cardMask' => $paymentData->objPmNotify->pan_masked,
            'tokenId' => $paymentData->objPmNotify->token_id,
            'tokenExpiresAt' => $paymentData->objPmNotify->token_expiration_date
        ]);
    }

    /**
     * Extract the token id that matches this payment's billable entity.
     *
     * @param Payment $payment
     * @return string|null
     */
    private function extractPaymentBillableToken(Payment $payment): string|null
    {
        if ($payment->billable) {
            if (in_array(Billable::class, class_uses_recursive($payment->billable), true)) {
                /** @var Billable $billable */
                $billable = $payment->billable;

                if ($billable->netopia_token) {
                    if (!$billable->netopia_token_expires_at || $billable->netopia_token_expires_at->isFuture()) {
                        return $billable->netopia_token;
                    }
                }
            }
        }

        return null;
    }

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
    public function executeSoap(Payment $payment): mixed
    {
        // Make sure that an account identifier has been configured
        if (!$this->_configuration->get('netopia.signature')) {
            throw new ConfigurationException('Signature not configured!');
        }

        // Make sure that there is a payment method attached to this payment
        if (!$payment->paymentMethod || ($payment->paymentMethod->token_expires_at instanceof Carbon && $payment->paymentMethod->token_expires_at->isPast())) {
            throw new NetopiaException('This payment does not have a payment method set.');
        }

        // Create the SOAP client
        $soap = new SoapClient(implode('/', [
            $this->baseUrl,
            'api',
            'payment2',
            '?wsdl'
        ]), [
            'cache_wsdl' => WSDL_CACHE_NONE
        ]);

        // Build the account object
        $account = new stdClass();
        $account->id = $this->_configuration->get('netopia.soap_signature');
        $account->user_name = $this->_configuration->get('netopia.username'); // please ask mobilPay to upgrade the necessary access required for token payments
        $account->customer_ip = '0.0.0.0'; // The buyer's IP address
        $account->confirm_url = $this->_urlGenerator->route('netopia.ipn');  // this is where mobilPay will send the payment result. This has priority over the SOAP call response

        // Build the transaction object
        $transaction = new stdClass();
        $transaction->paymentToken = $payment->paymentMethod->token_id;

        // Build the order object
        $order = new stdClass();
        $order->id = $payment->id; //your orderId. As with all mobilPay payments, it needs to be unique at seller account level
        $order->description = $payment->description; //payment descriptor
        $order->amount = $payment->amount; // order amount; decimals present only when necessary, i.e. 15 not 15.00

        if ($order->amount - (int) $order->amount <= 0) {
            $order->amount = (int) $order->amount;
        }

        $order->currency = $payment->currency; //currency
        $order->billing = $payment->billing_address;
        $order->shipping = $payment->shipping_address;

        // Build the HASH
        $account->hash = mb_strtoupper(sha1(mb_strtoupper($this->_configuration->get('netopia.account_password_hash')) . $order->id . $order->amount . $order->currency . $account->id));

        // Build the request object
        $req = new stdClass();
        $req->account = $account;
        $req->order = $order;
        $req->params = new stdClass();
        $req->transaction = $transaction;

        try {
            $this->_logManager->debug('SOAP request', [$req]);

            $response = $soap->doPayT(['request' => $req]);

            $this->_logManager->debug('SOAP response', [$response]);

            if (isset($response->errors) && $response->errors->code !== 0x00) {
                throw new Exception($response->code, $response->message);
            }

            return $response;
        } catch(SoapFault $e) {
            throw new Exception($e->faultstring);
        }
    }
}
