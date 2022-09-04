<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Exception;
use Illuminate\Http\{Request, Response as PlainResponse};
use Illuminate\Support\Facades\{Log, Response};

class PaymentReturnController
{
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
     * @throws Exception
     * @return PlainResponse
     */
    public function ipn(Request $request, PaymentService $paymentService): PlainResponse
    {
        $payment = $paymentService->decryptPayment($request->get('env_key'), $request->get('data'));

        Log::debug('Received IPN', [$request->get('env_key'), $request->get('data')]);

        return Response::view('netopia::payment_result', [
            'result' => $payment
        ])->withHeaders([
            'Content-Type' => 'application/xml'
        ]);
    }
}
