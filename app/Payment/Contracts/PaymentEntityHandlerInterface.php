<?php

namespace App\Payment\Contracts;

use App\Models\Payment;
use App\Models\Status;

interface PaymentEntityHandlerInterface
{
    public function handleStatusChange(Payment $payment, Status $status, int $source = 0): void;
}
