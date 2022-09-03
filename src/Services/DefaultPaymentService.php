<?php

namespace iRealWorlds\Netopia\Services;

use Exception;
use Illuminate\Support\Facades\{Config};
use iRealWorlds\Netopia\Contracts\PaymentService;
use iRealWorlds\Netopia\Entities\PaymentRequest;
use Netopia\Payment\{Invoice};
use Netopia\Payment\Request\Card;

/**
 * @template TBillable
 * @extends PaymentService<TBillable>
 */
class DefaultPaymentService extends PaymentService
{
    /**
     * Execute a Netopia payment.
     *
     * @param PaymentRequest<TBillable> $payment
     * @throws Exception
     * @return string
     */
    public function generatePaymentUri(PaymentRequest $payment): string
    {
        $paymentRequest = new Card();
        $paymentRequest->signature = Config::get('netopia.signature'); //signature - generated by mobilpay.ro for every merchant account
        $paymentRequest->orderId = $payment->id; // order_id should be unique for a merchant account
        $paymentRequest->confirmUrl = 'https://example.test/card/success'; // is where mobilPay redirects the client once the payment process is finished and is MANDATORY
        $paymentRequest->returnUrl = 'https://example.test/ipn'; // is where mobilPay will send the payment result and is MANDATORY

        // Invoices info
        $paymentRequest->invoice = new Invoice();
        $paymentRequest->invoice->currency = $payment->currency;
        $paymentRequest->invoice->amount = (string) $payment->amount;
        $paymentRequest->invoice->tokenId = null;
        $paymentRequest->invoice->details = $payment->description;

        // Billing Info
        $paymentRequest->invoice->setBillingAddress($payment->address);

        // Shipping
        $paymentRequest->invoice->setShippingAddress($payment->address);

        // encrypting
        $paymentRequest->encrypt($this->certificatePath);

        /**
         * Send the following data to NETOPIA Payments server
         * Method : POST
         * URL : $paymentUrl.
         */
        $envKey = $paymentRequest->getEnvKey();
        $data = $paymentRequest->getEncData();

        return $this->buildUrl('transfer', [
            'env_key' => $envKey,
            'data' => $data
        ]);
    }
}
