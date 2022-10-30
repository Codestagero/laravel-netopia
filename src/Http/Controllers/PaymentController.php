<?php

namespace Codestage\Netopia\Http\Controllers;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Entities\PaymentResult;
use Codestage\Netopia\Enums\PaymentStatus;
use Codestage\Netopia\Models\Payment;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PaymentController
{
    /**
     * PaymentController constructor method.
     *
     * @param PaymentService $_paymentService
     * @param ResponseFactory $_responseFactory
     * @param Repository $_configuration
     */
    public function __construct(
        private readonly PaymentService $_paymentService,
        private readonly ResponseFactory $_responseFactory,
        private readonly Repository $_configuration
    ) {
    }

    /**
     * Redirect the user to a payment URL.
     *
     * @param Request $request
     * @throws Exception
     * @throws Throwable
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $id = $request->route('payment');

        /** @var Payment $payment */
        $payment = Payment::query()->findOrFail($id);

        // Check if this payment needs more steps
        if (!\in_array($payment->status, $this->_configuration->get('netopia.payable_statuses'))) {
            throw new HttpException(403);
        }

        // If this payment actually has an amount lower than or equal to 0, we can skip the payment
        if ($payment->amount <= 0) {
            // Update the payment status
            $this->_paymentService->executePaymentResult($payment, new PaymentResult([
                'newStatus' => PaymentStatus::Confirmed,
                'paymentId' => $payment->getKey()
            ]));

            // Redirect to the success page
            return $this->_responseFactory->redirectToRoute('netopia.ipn');
        }

        // Redirect the user to the payment URL
        return $this->_responseFactory->view('netopia::execute_payment', [
            'payment' => $payment->encrypt()
        ]);
    }
}
