<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Config, Response};
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController
{
    /**
     * Redirect the user to a payment URL.
     *
     * @param Request $request
     * @throws Exception
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request): \Illuminate\Http\Response
    {
        $id = $request->route('payment');

        /** @var Payment $payment */
        $payment = Payment::query()->findOrFail($id);

        // Check if this payment needs more steps
        if (!\in_array($payment->status, Config::get('netopia.payable_statuses'))) {
            throw new HttpException(403);
        }

        return Response::view('netopia::execute_payment', [
            'payment' => $payment->encrypt()
        ]);
    }
}
