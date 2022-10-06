<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Events\PaymentStatusChangedEvent;
use Codestage\Netopia\Models\Payment;
use Codestage\Netopia\Traits\Billable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\{Model, ModelNotFoundException};
use Illuminate\Http\{Request, Response as PlainResponse};
use Illuminate\Support\Facades\{Response};
use Throwable;

class PaymentReturnController
{
    /**
     * PaymentReturnController constructor method.
     */
    public function __construct(private readonly Dispatcher $_eventDispatcher)
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

        // Remember the old status
        $oldStatus = $payment->status;

        // Update the payment status
        if ($ipn->newStatus !== null) {
            $payment->status = $ipn->newStatus;
        }

        // Save the changes applied on the payment model
        $payment->saveOrFail();

        // If a token was received with this payment, store it in the billable model
        if ($ipn->tokenId) {
            /** @var Model|Billable $billable */
            $billable = $payment->billable;
            $billable->netopia_token = $ipn->tokenId;
            $billable->netopia_token_expires_at = $ipn->tokenExpiresAt;

            $billable->save();
        }

        // Emit the new status event
        if ($ipn->newStatus !== null) {
            $this->_eventDispatcher->dispatch(new PaymentStatusChangedEvent($payment, $oldStatus, $ipn->newStatus, $ipn));
        }

        // Return a payment result XML
        return Response::view('netopia::payment_result', [
            'result' => $ipn
        ])->withHeaders([
            'Content-Type' => 'application/xml'
        ]);
    }
}
