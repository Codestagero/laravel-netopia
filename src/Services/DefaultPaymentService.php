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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Config, Log, URL};
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
     * Execute a Netopia payment.
     *
     * @param Payment $payment
     * @throws Exception
     * @return EncryptedPayment
     */
    public function generateEncryptedPayment(Payment $payment): EncryptedPayment
    {
        $paymentRequest = new Card();
        $paymentRequest->signature = Config::get('netopia.signature'); //signature - generated by mobilpay.ro for every merchant account
        $paymentRequest->orderId = $payment->getKey(); // order_id should be unique for a merchant account
        $paymentRequest->confirmUrl = URL::route('netopia.ipn'); // is where mobilPay redirects the client once the payment process is finished and is MANDATORY
        $paymentRequest->returnUrl = URL::route('netopia.success'); // is where mobilPay will send the payment result and is MANDATORY
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

        Log::debug('Encrypted payment', [
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

        Log::debug('Decrypted payment', [
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
    public function soapPayment(Payment $payment): mixed
    {
        // Make sure that an account identifier has been configured
        if (!Config::get('netopia.signature')) {
            throw new ConfigurationException('Signature not configured!');
        }

        // Make sure that the billable entity has a valid token
        /** @var Model|Billable $billable */
        $billable = $payment->billable;

        if (!$billable->netopia_token || ($billable->netopia_token_expires_at instanceof Carbon && $billable->netopia_token_expires_at->isPast())) {
            throw new NetopiaException('The billable entity must have a valid associated token.');
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
        $account->id = Config::get('netopia.signature');
        $account->user_name = Config::get('netopia.username'); // please ask mobilPay to upgrade the necessary access required for token payments
        $account->customer_ip = '0.0.0.0'; // The buyer's IP address
        $account->confirm_url = URL::route('netopia.ipn');  // this is where mobilPay will send the payment result. This has priority over the SOAP call response

        // Build the transaction object
        $transaction = new stdClass();
        $transaction->paymentToken = $billable->netopia_token;

        // Build the billing address object
        $billing = new stdClass();
        $billing->country = 'billing_country';
        $billing->county = 'billing_county';
        $billing->city = 'billing_city';
        $billing->address = 'billing_address';
        $billing->postal_code = 'billing_postal_code';
        $billing->first_name = 'billing_first_name';
        $billing->last_name = 'billing_last_name';
        $billing->phone = 'billing_phone';
        $billing->email = 'email_address';

        // Build the shipping address object
        $shipping = new stdClass();
        $shipping->country = 'shipping_country';
        $shipping->county = 'shipping_county';
        $shipping->city = 'shipping_city';
        $shipping->address = 'shipping_address';
        $shipping->postal_code = 'shipping_postal_code';
        $shipping->first_name = 'shipping_first_name';
        $shipping->last_name = 'shipping_last_name';
        $shipping->phone = 'shipping_phone';
        $shipping->email = 'shipping_email';

        // Build the order object
        $order = new stdClass();
        $order->id = $payment->id; //your orderId. As with all mobilPay payments, it needs to be unique at seller account level
        $order->description = $payment->description; //payment descriptor
        $order->amount = $payment->amount; // order amount; decimals present only when necessary, i.e. 15 not 15.00

        if ($order->amount - (int) $order->amount <= 0) {
            $order->amount = (int) $order->amount;
        }

        $order->currency = $payment->currency; //currency
        $order->billing = $billing;
        $order->shipping = $shipping;

        // Build the HASH
        $account->hash = mb_strtoupper(sha1(mb_strtoupper(md5(Config::get('netopia.account_password_hash'))) . "{$order->id}{$order->amount}{$order->currency}{$account->id}"));

        // Build the request object
        $req = new stdClass();
        $req->account = $account;
        $req->order = $order;
        $req->params = new stdClass();
        $req->transaction = $transaction;

        try {
            Log::debug('SOAP request', [$req]);

            $response = $soap->doPayT(['request' => $req]);

            Log::debug('SOAP response', [$response]);

            if (isset($response->errors) && $response->errors->code !== 0x00) {
                throw new Exception($response->code, $response->message);
            }

            return $response;
        } catch(SoapFault $e) {
            throw new Exception($e->faultstring);
        }
    }
}
