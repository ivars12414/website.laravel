<?php

namespace App\Payment\Gateways;

use App\Models\Client;
use App\Models\Country;
use App\Models\CreditsTransaction;
use App\Models\Order;
use App\Models\Payment;
use convertiq;
use App\Payment\Contracts\PaymentGatewayInterface;

class ConvertiqGateway implements PaymentGatewayInterface
{
  private string $project_id;
  private string $secret_key;
  private int $best_before;

  public function __construct()
  {
    $this->project_id = trim(env('CQ_PROJECT_ID'));
    $this->secret_key = trim(env('CQ_SECRET_KEY'));
    $this->best_before = (int)env('CQ_BEST_BEFORE');
  }

  public function createPayment(array $data): array
  {
    try {
      if (empty($this->project_id)) {
        throw new \Exception('Empty project ID');
      }

      if (empty($this->secret_key)) {
        throw new \Exception('Empty secret key');
      }

      $host = 'https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']);

      $amount = (int)($data['sum'] * 100);
      $currencyCode = trim(strtoupper($data['currency_code']));
      $bestBeforeDate = new \DateTime();
      $bestBeforeDate->setTimestamp(time() + $this->best_before);

      $paymentData = Payment::find($data['payment_id']);
      $paymentID = md5($paymentData->id . time() . uniqid());

      switch ($paymentData['type']) {
        case Payment::ORDER_TYPE_TOP_UP:
          /** @var CreditsTransaction $transaction */
          $transaction = CreditsTransaction::find($paymentData->order_id);
          $langId = $transaction->lang_id > 0 ? (int)$transaction->lang_id : getMainLang();
          $langCode = getLangCodeById($langId);
          $urls = [
                  'success' => $paymentData->success_url,
                  'fail' => $paymentData->failed_url,
          ];
          $client = $transaction->client;
          $phone_code = Country::findByHash($client->phone_country, $transaction->lang_id)->phonecode ?? '';
          $phone = trim("$phone_code $client->phone");
          $customer = [
                  'id' => $client->id,
                  'firstName' => $client->name,
                  'lastName' => $client->surname ?: '',
                  'email' => $client->mail,
                  'phone' => preg_match("/^\d+ \d+$/", $phone) ? $phone : '',
          ];
          $descr = $_SERVER['HTTP_HOST'] . " | Top up " . currency($transaction->price_in_currency, $transaction->currency_code)->format(withStrongInt: false);
          break;
        case Payment::ORDER_TYPE_ORDER:
          /** @var Order $transaction */
          $transaction = Order::find($paymentData->order_id);
          $langId = $transaction->lang_id > 0 ? (int)$transaction->lang_id : getMainLang();
          $langCode = getLangCodeById($langId);
          $urls = [
                  'success' => $paymentData->success_url,
                  'fail' => $paymentData->failed_url,
          ];
          $client = $transaction->client;
          $phone_code = Country::findByHash($client->phone_country, $transaction->lang_id)->phonecode ?? '';
          $phone = trim("$phone_code $client->phone");
          $customer = [
                  'id' => $client->id,
                  'firstName' => $client->name,
                  'lastName' => $client->surname ?: '',
                  'email' => $client->mail,
                  'phone' => preg_match("/^\d+ \d+$/", $phone) ? $phone : '',
          ];
          $descr = $_SERVER['HTTP_HOST'] . " | Top up " . currency($transaction->getTotal())->convert($transaction->currency_code, $transaction->currency_rate)->format(withStrongInt: false);
          break;
        default:
          throw new \Exception('Unknown payment type');
      }

      $gate = new Convertiq\Gate($this->secret_key);
      $gate->setValidationParams(false);
      // Secret key
      $payment = new Convertiq\Payment($this->project_id, $paymentID);
      // Project ID and payment ID and payment ID must be unique within your project scope
      $payment->setPaymentAmount($amount)->setPaymentCurrency($currencyCode);
      // Amount in minor currency units and currency in ISO-4217 alpha-3 format
      $payment->setBestBefore($bestBeforeDate);
      // Date and time for timer countdown
      $payment->setLanguageCode($langCode);
      // Language code to use in payment form

      if (!empty($descr)) {
        $payment->setPaymentDescription($descr);
      }

      $payment->setCustomerId($customer['id']);
      $payment->setCustomerFirstName($customer['firstName']);
      if (!empty($customer['lastName'])) {
        $payment->setCustomerLastName($customer['lastName']);
      }
      $payment->setCustomerEmail($customer['email']);
      if (!empty($customer['phone'])) {
        $payment->setCustomerPhone($customer['phone']);
      }

      $payment->setMerchantSuccessUrl($urls['success']);
      $payment->setMerchantFailUrl($urls['fail']);
      $payment->setRedirectSuccessUrl($urls['success']);
      $payment->setRedirectFailUrl($urls['fail']);

      $payment->setMerchantCallbackUrl("$host/api/webhook/convertiq");

      $file = __DIR__ . '/../../../lgs/cq.log';

      file_put_contents(
              $file,
              PHP_EOL . PHP_EOL . print_r($payment->getParams(), true) . PHP_EOL . '-------------' . PHP_EOL,
              FILE_APPEND
      );

      ob_start();
      $url = $gate->getPurchasePaymentPageUrl($payment);
      $output = ob_get_contents();
      ob_clean();
      ob_end_clean();

      return [
              'error' => false,
              'payment_url' => $url,
              'gateway_payment_id' => $paymentID,
              'output' => $output,
      ];
    } catch (Convertiq\Exception\ValidationException $e) {
      return [
              'error' => true,
              'msg' => implode('<br> ', $e->getErrors()),
      ];
    } catch (\Throwable $e) {
      return [
              'error' => true,
              'msg' => $e->getMessage(),
      ];
    }
  }

  public function processPayment(string $paymentId): array
  {
    return ['error' => false];
  }

  public function refundPayment(string $paymentId): array
  {
    return ['error' => false];
  }
}
