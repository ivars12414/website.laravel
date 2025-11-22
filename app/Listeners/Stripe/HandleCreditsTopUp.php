<?php

namespace App\Listeners\Stripe;

use App\Models\Payment;
use App\Models\Status;
use App\Payment\PaymentService;

class HandleCreditsTopUp
{
    public function handle($event)
    {
        $session = $event->payload['data']['object'];

        if (($session['metadata']['operation'] ?? null) !== Payment::ORDER_TYPE_TOP_UP) {
            return;
        }

        $paymentID = $session['id'];

        $paymentService = new PaymentService();
        $transaction = Payment::where('gateway_payment_id', $paymentID)->first();
        $status = Status::findByLabel('paid', 'payments');
        $paymentService->updatePaymentStatus(
            $transaction,
            $status
        );

    }
}
