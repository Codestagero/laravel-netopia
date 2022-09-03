<?php

namespace Codestage\Netopia\Http;

use Codestage\Netopia\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Response;

class PaymentController
{
    /**
     * Redirect the user to a payment URL.
     *
     * @param Payment $payment
     * @throws Exception
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Payment $payment): \Illuminate\Http\Response
    {
        return Response::view('netopia::execute_payment', [
            'payment' => $payment->encrypt()
        ]);
    }
}
