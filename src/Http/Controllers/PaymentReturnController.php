<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Response};

class PaymentReturnController
{
    /**
     * Process a Netopia payment result.
     *
     * @param Request $request
     * @param PaymentService $paymentService
     * @throws Exception
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, PaymentService $paymentService): \Illuminate\Http\Response
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
