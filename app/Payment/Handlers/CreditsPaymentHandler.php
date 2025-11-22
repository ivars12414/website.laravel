<?php

namespace App\Payment\Handlers;

use App\Models\CreditsTransaction;
use App\Models\Payment;
use App\Models\Status;
use App\Models\StatusReason;
use App\Payment\Contracts\PaymentEntityHandlerInterface;

class CreditsPaymentHandler implements PaymentEntityHandlerInterface
{
    public function handleStatusChange(Payment $payment, Status $status, int $source = 0): void
    {
        $transaction = CreditsTransaction::find($payment->order_id);
        if (!$transaction) throw new \Exception('Transaction not found');
        switch ($status->label) {
            case 'paid':
                $transaction->setStatus(
                    status: Status::findByLabel('credited', 'top_up'),
                    source: $source
                );
                break;
            case 'refunded':
                $canceledStatus = Status::findByLabel('canceled', 'top_up');
                $transaction->setStatus(
                    status: $canceledStatus,
                    source: $source,
                    reason_hash: StatusReason::findByLabel('refunded', $canceledStatus)->hash
                );
                break;
            case 'canceled':
                $canceledStatus = Status::findByLabel('canceled', 'top_up');
                $transaction->setStatus(
                    status: $canceledStatus,
                    source: $source,
                    reason_hash: StatusReason::findByLabel('by_client', $canceledStatus)->hash
                );
                break;
            case 'declined':
                $canceledStatus = Status::findByLabel('canceled', 'top_up');
                $transaction->setStatus(
                    status: $canceledStatus,
                    source: $source,
                    reason_hash: StatusReason::findByLabel('declined', $canceledStatus)->hash
                );
                break;
        }
    }
}
