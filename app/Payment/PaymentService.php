<?php

namespace App\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Models\StatusLog;
use App\Payment\Exceptions\PaymentRegistryException;
use App\Payment\Registry\PaymentGatewayRegistry;
use App\Payment\Registry\PaymentHandlerRegistry;

class PaymentService
{
    public function createPayment(string $type, int $orderId, string $paymentMethodHash, float $sum, string $currencyCode, array $urls, int $source = 0): array
    {
        if ($sum <= 0) {
            return ['error' => true, 'msg' => 'The amount must be greater than 0'];
        }

        if (!in_array($type, array_keys(Payment::ORDER_TYPES))) {
            return ['error' => true, 'msg' => 'Not valid order type'];
        }

        // Получаем метод оплаты
        $paymentMethod = PaymentMethod::whereActive()
            ->where('hash', $paymentMethodHash)
            ->first();

        if (!$paymentMethod) {
            return ['error' => true, 'msg' => 'Not valid payment method'];
        }

        // Создаем запись платежа
        $payment = Payment::create([
            'type' => $type,
            'order_id' => $orderId,
            'sum' => $sum,
            'currency_code' => $currencyCode,
            'payment_method_hash' => $paymentMethodHash,
            'success_url' => $urls['success'],
            'canceled_url' => $urls['cancel'],
            'failed_url' => $urls['failed'],
        ]);

        $urls['success'] = str_replace('%tx_id%', $payment->id, $urls['success']);
        $urls['cancel'] = str_replace('%tx_id%', $payment->id, $urls['cancel']);
        $urls['failed'] = str_replace('%tx_id%', $payment->id, $urls['failed']);

        $payment->update([
            'success_url' => $urls['success'],
            'canceled_url' => $urls['cancel'],
            'failed_url' => $urls['failed'],
        ]);

        $this->updatePaymentStatus(
            payment: $payment,
            status: Status::findByLabel('awaiting', 'payments'),
            add_data: [
                'source' => $source,
            ]
        );

        try {
            // Получаем платежный шлюз из registry
            $gateway = PaymentGatewayRegistry::resolve($paymentMethod->label);

            // Создаем платеж через выбранный шлюз
            $result = $gateway->createPayment([
                'payment_id' => $payment->id,
                'sum' => $sum,
                'currency_code' => $currencyCode,
                'success_url' => $urls['success'],
                'cancel_url' => $urls['cancel'],
                'failed_url' => $urls['failed'],
                'type' => $type,
            ]);

            if (!$result['error']) {
                if (!empty($result['gateway_payment_id'])) {
                    $payment->update([
                        'gateway_payment_id' => $result['gateway_payment_id']
                    ]);
                }

                return [
                    'error' => false,
                    'href' => $result['payment_url'] ?? '',
                    'payment_id' => $payment->id
                ];
            }

            return $result;
        } catch (PaymentRegistryException $e) {
            return [
                'error' => true,
                'msg' => 'Payment method is not available: ' . $e->getMessage()
            ];
        }
    }

    public function updatePaymentStatus(Payment $payment, Status $status, array $add_data = []): array
    {
        // Обновление статуса
        $payment->status()->associate($status);
        $payment->save();

        // Запись в лог
        StatusLog::logChange(
            $payment->id,
            $status,
            0,
            (int)$add_data['source'] ?? 0,
            $add_data['reason_hash'] ?? ''
        );

        // Уведомляем сущность через handler из registry
        $handler = PaymentHandlerRegistry::resolve($payment->type);
        if ($handler) {
            $handler->handleStatusChange($payment, $status, (int)$add_data['source'] ?? 0);
        }

        return ['error' => false, 'msg' => 'Payment status changed'];
    }

    public function updatePaymentStatusByGatewayPaymentId(string $gatewayPaymentId, string $statusLabel, string $category, array $add_data = []): array
    {
        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)->first();

        if (!$payment) {
            return ['error' => true, 'msg' => 'Payment not found'];
        }

        $status = Status::findByLabel($statusLabel, $category);

        if (!$status) {
            return ['error' => true, 'msg' => 'Status not found'];
        }

        if ((int)$payment->status_id === (int)$status->id) {
            return ['error' => false, 'msg' => 'Payment status unchanged'];
        }

        return $this->updatePaymentStatus($payment, $status, $add_data);
    }

    /**
     *
     * @param string $order_type key from Payment::ORDER_TYPES
     * @param string $view select || radio
     *
     */
    function paymentMethodBlock(string $order_type, string $view = 'select'): string
    {
        $payment_methods = \App\Models\PaymentMethod::whereActive()->where('lang_id', lang()->id)->get();

        $payment_methods = $payment_methods->filter(function ($payment_method) use ($order_type) {
            return in_array($order_type, $payment_method->order_types ?? []);
        });

        ob_start();
        if ($payment_methods->count()) {
            if ($payment_methods->count() > 1) { ?>
                <?php
                switch ($view) {
                    case 'radio':
                        ?>
                        <div class="form__block">
                            <?php foreach ($payment_methods as $record) { ?>
                                <div class="radio">
                                    <input type="radio" class="" name="payment_method" id="p_m_<?= $record->id ?>"
                                           value="<?= $record->hash ?>">
                                    <label for="p_m_<?= $record->id ?>">
                                        <img src="/userfiles/payment_methods/<?= $record->icon ?>" height="20" alt="">
                                        <?= $record->name ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                        break;
                    default:
                        ?>
                        <div class="form__block">
                            <div class="select">
                                <select name="payment_method" class="js-select">
                                    <?php foreach ($payment_methods as $record) { ?>
                                        <option value="<?= $record->hash ?>"><?= $record->name ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php
                        break;
                }
                ?>
            <?php } else { ?>
                <input type="hidden" name="payment_method" value="<?= $payment_methods->first()->hash ?>">
            <?php }
        }
        $html = ob_get_contents();
        ob_clean();
        ob_end_clean();

        return $html;
    }
}
