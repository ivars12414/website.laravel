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
        try {
            $credits = CreditService::convertFromFiat($data['sum'], $data['currency_code']);
//      $credits = $data['sum'];

            if ($credits <= 0) {
                throw new \Exception('Invalid credits amount');
            }

            // Получаем клиента из сессии
            $client = client();

            // Проверяем баланс
            if ($client->balance < $credits) {
                return [
                    'error' => true,
                    'msg' => 'Insufficient credits balance'
                ];
            }

            // Списываем кредиты через метод модели
            $client->changeBalance(
                credits: -$credits,
                log_type: BalanceLog::TYPE_ORDER,
                type_id: $data['payment_id']
            );

            $payment = Payment::find($data['payment_id']);
            new PaymentService()->updatePaymentStatus(
                payment: $payment,
                status: Status::findByLabel('paid', 'payments'),
                add_data: []
            );

            return [
                'error' => false,
                'payment_url' => $data['success_url'],
                'credits_spent' => $credits
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'payment_url' => $data['failed_url'],
                'msg' => $e->getMessage()
            ];
        }
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
