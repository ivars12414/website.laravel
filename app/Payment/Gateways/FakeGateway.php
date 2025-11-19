<?php

namespace App\Payment\Gateways;

use App\Models\Payment;
use App\Payment\Contracts\PaymentGatewayInterface;

class FakeGateway implements PaymentGatewayInterface
{
  /**
   * Создаёт "оплату" без реального биллинга.
   * Ждёт в $data:
   * - payment_id        (int|string) ID платежа в БД
   * - success_url       (string)
   * - failed_url        (string)
   */
  public function createPayment(array $data): array
  {
    try {
      // 1. Получаем платеж
      $payment = Payment::find($data['payment_id']);
      if (!$payment) {
        throw new \Exception('Payment not found');
      }

      $query = http_build_query([
              'payment_id' => $payment->id,
              'order' => $payment->order_id,
              'amount' => (int)round(((float)$payment->sum) * 100),
              'success_url' => $payment->success_url,
              'failed_url' => $payment->failed_url,
              'canceled_url' => $payment->canceled_url,
      ]);

      return [
              'error' => false,
              'payment_url' => '/fake_gate.php?' . $query,
      ];

    } catch (\Exception $e) {
      return [
              'error' => true,
              'payment_url' => $data['failed_url'] ?? null,
              'msg' => $e->getMessage(),
      ];
    }
  }

  /**
   * Для реальных гейтов тут шёл бы коллбек/вебхук,
   * фейковый шлюз использует отдельный ручной коллбек.
   */
  public function processPayment(string $paymentId): array
  {
    $payment = Payment::find($paymentId);

    if (!$payment) {
      return [
              'error' => true,
              'msg' => 'Payment not found',
      ];
    }

    return [
            'error' => false,
            'payment_id' => $payment->id,
            'status' => optional($payment->status)->label ?? null,
    ];
  }

  /**
   * Возврат для фейка можно всегда считать успешным
   * (или тут тоже проставлять статус refund_requested / refunded,
   * если у тебя такие статусы есть).
   */
  public function refundPayment(string $paymentId): array
  {
    // Можем сразу пометить платеж как refunded, если хочешь.
    // Я оставлю просто успешный ответ.
    return ['error' => false];
  }
}
