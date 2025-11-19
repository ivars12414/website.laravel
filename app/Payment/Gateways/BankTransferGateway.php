<?php

namespace App\Payment\Gateways;

use App\Models\Payment;
use App\Payment\Contracts\PaymentGatewayInterface;

class BankTransferGateway implements PaymentGatewayInterface
{
  private array $bankDetails;

  public function __construct()
  {
    $this->bankDetails = [
            'bank_name' => getConfig('bank_name'),
            'account_number' => getConfig('bank_account'),
            'swift' => getConfig('bank_swift'),
            'recipient' => getConfig('bank_recipient'),
            'description_template' => getConfig('bank_transfer_description')
    ];
  }

  public function createPayment(array $data): array
  {
    try {
      $description = str_replace(
              '%payment_id%',
              $data['payment_id'],
              $this->bankDetails['description_template']
      );

      // Формируем URL для страницы с реквизитами, добавляя всю необходимую информацию
      $bankDetailsUrl = '/bank_details.php?' . http_build_query([
                      'payment_id' => $data['payment_id'],
                      'sum' => $data['sum'],
                      'currency' => $data['currency_code'],
                      'description' => $description,
                      'bank_name' => $this->bankDetails['bank_name'],
                      'account' => $this->bankDetails['account_number'],
                      'swift' => $this->bankDetails['swift'],
                      'recipient' => $this->bankDetails['recipient'],
                      'success_url' => $data['success_url'],
                      'cancel_url' => $data['cancel_url']
              ]);

      return [
              'error' => false,
              'payment_url' => $bankDetailsUrl,
              'bank_details' => $this->bankDetails,
              'description' => $description
      ];
    } catch (\Exception $e) {
      return [
              'error' => true,
              'msg' => $e->getMessage()
      ];
    }
  }

  public function processPayment(string $paymentId): array
  {
    // Банковский перевод обрабатывается вручную администратором
    return [
            'error' => false,
            'msg' => 'Bank transfer should be processed manually'
    ];
  }

  public function refundPayment(string $paymentId): array
  {
    try {
      $payment = Payment::find($paymentId);
      if (!$payment) {
        throw new \Exception('Payment not found');
      }

      // Проверяем, что платёж был успешно оплачен
      if ($payment->status->label !== 'paid') {
        throw new \Exception('Payment is not in paid status');
      }

      // Создаём запись о возврате
      $payment->refund_tm = time();
      $payment->save();

      return [
              'error' => false,
              'msg' => 'Refund request created successfully'
      ];
    } catch (\Exception $e) {
      return [
              'error' => true,
              'msg' => $e->getMessage()
      ];
    }
  }

  public function validatePayment(string $paymentId, array $data): array
  {
    try {
      $payment = Payment::find($paymentId);
      if (!$payment) {
        throw new \Exception('Payment not found');
      }

      // Проверяем сумму платежа
      if ((float)$data['amount'] !== (float)$payment->sum) {
        throw new \Exception('Invalid payment amount');
      }

      // Проверяем валюту
      if ($data['currency'] !== $payment->currency_code) {
        throw new \Exception('Invalid currency');
      }

      // Проверяем описание платежа
      $expectedDescription = str_replace(
              '%payment_id%',
              $payment->id,
              $this->bankDetails['description_template']
      );

      if ($data['description'] !== $expectedDescription) {
        throw new \Exception('Invalid payment description');
      }

      return [
              'error' => false,
              'payment' => $payment
      ];
    } catch (\Exception $e) {
      return [
              'error' => true,
              'msg' => $e->getMessage()
      ];
    }
  }
}