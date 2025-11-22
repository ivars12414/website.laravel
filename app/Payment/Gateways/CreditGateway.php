<?php

namespace App\Payment\Gateways;

use App\Models\BalanceLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Status;
use App\Payment\Contracts\PaymentGatewayInterface;
use App\Payment\PaymentService;
use App\Services\CreditService;

class CreditGateway implements PaymentGatewayInterface
{
    public function createPayment(array $data): array
    {
        return ['error' => false, 'href' => $data['success_url']];
    }

    public function processPayment(string $paymentId): array
    {
        // Платеж уже обработан при создании
        return ['error' => false];
    }

    public function refundPayment(string $paymentId): array
    {
        return ['error' => false];
    }
}
