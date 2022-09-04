<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Entities\PaymentResult;
use Codestage\Netopia\Enums\PaymentStatus;
use Exception;
use Illuminate\Http\{Request, Response as PlainResponse};
use Illuminate\Support\Facades\{Log, Response};
use Netopia\Payment\Request\PaymentAbstract;

class PaymentReturnController
{
    /**
     * Process a Netopia payment result.
     *
     * @return PlainResponse
     */
    public function get(): PlainResponse
    {
        return Response::view('netopia::payment_result', [
            'result' => new PaymentResult(
                PaymentStatus::Pending,
                PaymentAbstract::ERROR_CONFIRM_INVALID_POST_METHOD,
                PaymentAbstract::CONFIRM_ERROR_TYPE_PERMANENT,
                'invalid request metod for payment confirmation'
            )
        ])->withHeaders([
            'Content-Type' => 'application/xml'
        ]);
    }

    /**
     * Process a Netopia payment result.
     *
     * @param Request $request
     * @param PaymentService $paymentService
     * @throws Exception
     * @return PlainResponse
     */
    public function post(Request $request, PaymentService $paymentService): PlainResponse
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
