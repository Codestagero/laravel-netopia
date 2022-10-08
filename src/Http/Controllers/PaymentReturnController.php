<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Models\Payment;
use Codestage\Netopia\Traits\Billable;
use Illuminate\Database\Eloquent\{Model, ModelNotFoundException};
use Illuminate\Http\{Request, Response as PlainResponse};
use Illuminate\Support\Facades\{Response};
use Throwable;

class PaymentReturnController
{
    /**
     * PaymentReturnController constructor method.
     */
    public function __construct()
    {
    }

    /**
     * Process a Netopia payment result.
     *
     * @return PlainResponse
     */
    public function success(): PlainResponse
    {
        return Response::view('netopia::payment_success');
    }

    /**
     * Process a Netopia payment result.
     *
     * @param Request $request
     * @param PaymentService $paymentService
     * @throws ModelNotFoundException
     * @throws Throwable
     * @return PlainResponse
     */
    public function ipn(Request $request, PaymentService $paymentService): PlainResponse
    {
        // Decrypt the IPN data
        $ipn = $paymentService->decryptPayment($request->get('env_key'), $request->get('data'));

        // If the IPN has no order id attached, no payment can be associated
        if ($ipn->paymentId === null) {
            throw new ModelNotFoundException();
        }

        // Find the payment this notification is for
        /** @var Payment $payment */
        $payment = Payment::query()->findOrFail($ipn->paymentId);

        // Update the payment status
        $paymentService->executePaymentResult($payment, $ipn);

        // If a token was received with this payment, store it in the billable model
        if ($ipn->tokenId) {
            /** @var Model|Billable $billable */
            $billable = $payment->billable;
            $billable->netopia_token = $ipn->tokenId;
            $billable->netopia_token_expires_at = $ipn->tokenExpiresAt;

            $billable->save();
        }

        // Return a payment result XML
        return Response::view('netopia::payment_result', [
            'result' => $ipn
        ])->withHeaders([
            'Content-Type' => 'application/xml'
        ]);
    }
}
