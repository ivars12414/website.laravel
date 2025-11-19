<?php

namespace App\Payment\Handlers;

use App\Cart\CartManager;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Status;
use App\Models\StatusCategory;
use App\Models\StatusReason;
use App\Payment\Contracts\PaymentEntityHandlerInterface;
use App\Services\EsimAccess\OrderCreator;
use App\Services\OrderService;
use Throwable;

class OrderPaymentHandler implements PaymentEntityHandlerInterface
{
  public function handleStatusChange(Payment $payment, Status $status): void
  {
    $orderService = new OrderService();
    $order = Order::find($payment->order_id);
    if (!$order) {
      return;
    }
    $statuses = StatusCategory::findByLabel('orders')->statuses->keyBy('label');

    switch ($status->label) {
      case 'paid':
        CartManager::setCartFrozen();
        $orderService->sendMailToClient($order);

        $esimOrderResponse = $orderService->createEsimAccessOrder($order);
        if (!empty($esimOrderResponse['obj']['orderNo'])) {
          $order->esim_order_nr = $esimOrderResponse['obj']['orderNo'];
          $order->save();
        }

        $tgStartText = 'Order paid - ';
        $tgText = [];
        $tgText[] = 'E-mail: ' . $order->mail;
        $tgText[] = 'IP: ' . getIp();
        $tgText[] = 'Order Nr: ' . $order->nr;
        $tgText[] = 'Total amount: ' . currency($order->getTotal())->convert($order->currency_code, $order->currency_rate)->format(withStrongInt: false);
        insertTelegramNotification($tgStartText . implode(". ", $tgText));

        $orderService->updateOrderStatus($order, $statuses['completed'], SOURCE_SITE);

        break;
      case 'refunded':
        $orderService->updateOrderStatus($order, $statuses['canceled'], SOURCE_SITE, ['reason_hash' => StatusReason::findByLabel('refund', $statuses['canceled'])->hash]);
        break;
      case 'canceled':
        $orderService->updateOrderStatus($order, $statuses['canceled'], SOURCE_SITE, ['reason_hash' => StatusReason::findByLabel('by_client', $statuses['canceled'])->hash]);
        break;
      case 'declined':
        $orderService->updateOrderStatus($order, $statuses['canceled'], SOURCE_SITE, ['reason_hash' => StatusReason::findByLabel('declined', $statuses['canceled'])->hash]);
        break;
    }
  }
}