<?php

namespace Codestage\Netopia\Jobs;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Models\Payment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\LogManager;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Queue\Middleware\WithoutOverlapping;
use stdClass;

class ExecuteSoapPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public readonly Payment $payment)
    {
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->payment->getKey()))->dontRelease()];
    }

    /**
     * Execute the job.
     *
     * @param PaymentService $paymentService
     * @param LogManager $logManager
     * @param Repository $configuration
     * @param UrlGenerator $generator
     * @throws Exception
     * @return void
     */
    public function handle(PaymentService $paymentService, LogManager $logManager, Repository $configuration, UrlGenerator $generator): void
    {
        if (!$this->payment->paymentMethod) {
            throw new Exception('Tried to execute SOAP payment for an entity that has no associated payment method (' . $this->payment->getKey() . ').');
        }

        $logManager->debug('Executing SOAP payment.', [
            'payment' => $this->payment
        ]);

        try {
            $result = $paymentService->executeSoap($this->payment);
        } catch (Exception $e) {
            // Build the account object
            $account = new stdClass();
            $account->id = $configuration->get('netopia.soap_signature');
            $account->user_name = $configuration->get('netopia.username'); // please ask mobilPay to upgrade the necessary access required for token payments
            $account->customer_ip = '0.0.0.0'; // The buyer's IP address
            $account->confirm_url = $generator->route('netopia.ipn');  // this is where mobilPay will send the payment result. This has priority over the SOAP call response

            // Build the order object
            $order = new stdClass();
            $order->id = $this->payment->id; //your orderId. As with all mobilPay payments, it needs to be unique at seller account level
            $order->description = $this->payment->description; //payment descriptor
            $order->amount = $this->payment->amount; // order amount; decimals present only when necessary, i.e. 15 not 15.00

            if ($order->amount - (int) $order->amount <= 0) {
                $order->amount = (int) $order->amount;
            }

            $order->currency = $this->payment->currency; //currency
            $order->billing = $this->payment->billing_address;
            $order->shipping = $this->payment->shipping_address;

            $logManager->debug('Exception thrown when executing SOAP payment.', [
                'account' => $account,
                'order' => $order,
                'h' => mb_strtoupper($configuration->get('netopia.account_password_hash')) . $order->id . $order->amount . $order->currency . $account->id,
                'exception' => $e,
                'payment' => $this->payment,
            ]);

            return;
        }

        $logManager->debug('Executed SOAP payment.', [
            'result' => $result,
            'payment' => $this->payment,
        ]);
    }
}
