<?php

namespace App\Listeners\Stripe;

use App\Models\Payment;
use App\Models\Status;
use App\Payment\PaymentService;
use Illuminate\Support\Facades\Log;

class HandleCreditsTopUp
{
    public function handle($event)
    {

//        Log::info('[Stripe] HandleCreditsTopUp fired', [
//            'event_class' => get_class($event),
//            'event_type' => $event->payload['type'] ?? null,
//            'session_id' => $event->payload['data']['object']['id'] ?? null,
//            'metadata' => $event->payload['data']['object']['metadata'] ?? null,
//        ]);

        $session = $event->payload['data']['object'];

        if (($session['metadata']['operation'] ?? null) !== Payment::ORDER_TYPE_TOP_UP) {
            return;
        }

        $paymentService = new PaymentService();
        $paymentService->updatePaymentStatusByGatewayPaymentId($session['id'], 'paid', 'payments', [
            'source' => SOURCE_SITE
        ]);

    }
}
