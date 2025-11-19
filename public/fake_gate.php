<?php

use App\Models\StatusCategory;
use App\Payment\PaymentRegistrar;
use Illuminate\Support\Collection;

include $_SERVER['DOCUMENT_ROOT'] . "/inc/mysql.inc.php";
include $_SERVER['DOCUMENT_ROOT'] . "/inc/func.php";
include $_SERVER['DOCUMENT_ROOT'] . "/inc/LC.php";
include $_SERVER['DOCUMENT_ROOT'] . "/inc/sess_site.php";

$langId = getMainLang();

PaymentRegistrar::register();

$paymentId = trim((string)($_GET['payment_id'] ?? ''));
$orderId = trim((string)($_GET['order'] ?? $_GET['order_id'] ?? ''));
$amountParam = $_GET['amount'] ?? null;
$amountCents = null;
$displayAmount = null;

if ($amountParam !== null && $amountParam !== '') {
  if (!is_numeric($amountParam)) {
    $amountCents = null;
  } else {
    $amountCents = (int)$amountParam;
    $displayAmount = number_format($amountCents / 100, 2, '.', '');
  }
}

$successUrl = (string)($_GET['success_url'] ?? '');
$failedUrl = (string)($_GET['failed_url'] ?? '');
$canceledUrl = (string)($_GET['canceled_url'] ?? '');

$statusCategory = StatusCategory::findByLabel('payments');
$statuses = $statusCategory?->statuses ?? new Collection();

if (!($statuses instanceof Collection)) {
  $statuses = new Collection($statuses ?? []);
}

$statuses = $statuses->sortBy('sort')->values();
$statusesByLabel = $statuses->keyBy('label');

$errors = [];
$successMessage = null;
$selectedStatusLabel = null;
$webhookResponse = null;

if ($paymentId === '') {
  $errors[] = 'Не передан payment_id.';
}

if ($amountParam !== null && $amountParam !== '' && !is_numeric($amountParam)) {
  $errors[] = 'Некорректная сумма.';
}

if (isset($_POST['submit'])) {
  $selectedStatusLabel = strtolower(trim((string)($_POST['pay_status'] ?? '')));
  $selectedStatus = $statusesByLabel->get($selectedStatusLabel);

  if (!$selectedStatus) {
    $errors[] = 'Status not found';
  }

  if (!$errors) {
    $webhookResponse = sendFakeWebhook($paymentId, $selectedStatusLabel, [
            'order' => $orderId,
            'amount' => $amountCents,
    ]);

    if (($webhookResponse['error'] ?? false) === true) {
      $message = $webhookResponse['msg'] ?? 'Failed to send webhook';
      $errors[] = 'Webhook error: ' . $message;
    } else {
      $successMessage = sprintf('Webhook доставлен. Статус "%s" обновлён обработчиком.', $selectedStatus->name);

      $redirectUrl = resolveRedirectUrl($selectedStatus->label, [
              'success' => $successUrl,
              'failed' => $failedUrl,
              'canceled' => $canceledUrl,
      ]);

      if (!empty($_POST['auto_redirect'])) {
        redirectWithTimeout($redirectUrl, 1500);
        exit;
      }
    }
  }
}

function resolveRedirectUrl(string $statusLabel, array $urls): string
{
  $successUrl = $urls['success'] ?? '';
  $failedUrl = $urls['failed'] ?? '';
  $canceledUrl = $urls['canceled'] ?? '';

  switch ($statusLabel) {
    case 'paid':
      return $successUrl ?: '/';
    case 'canceled':
      return $canceledUrl ?: ($failedUrl ?: ($successUrl ?: '/'));
    case 'declined':
    case 'failed':
      return $failedUrl ?: ($successUrl ?: '/');
    default:
      return $successUrl ?: '/';
  }
}

function sendFakeWebhook(string $paymentId, string $statusLabel, array $payload = []): array
{
  if (!function_exists('curl_init')) {
    return ['error' => true, 'msg' => 'cURL extension is not available'];
  }

  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
  $url = sprintf('%s://%s/api/webhook/fake', $scheme, $host);

  $payload = array_filter($payload, static function ($value) {
    return $value !== null && $value !== '';
  });

  $data = array_merge($payload, [
          'payment_id' => $paymentId,
          'status' => $statusLabel,
  ]);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));

  $body = curl_exec($ch);
  $curlError = curl_error($ch);
  $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($body === false || $curlError) {
    return ['error' => true, 'msg' => $curlError ?: 'Webhook request failed'];
  }

  $decoded = json_decode((string)$body, true);
  if (!is_array($decoded)) {
    $decoded = [];
  }

  if ($statusCode >= 400 || ($decoded['error'] ?? false)) {
    return [
            'error' => true,
            'msg' => $decoded['msg'] ?? 'Webhook request failed',
            'response' => $decoded,
            'status_code' => $statusCode,
    ];
  }

  return [
          'error' => false,
          'response' => $decoded,
          'status_code' => $statusCode,
  ];
}

function renderAlert(string $message, string $type = 'alert--green'): void
{
  echo '<div class="alert ' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Тестовый платеж</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #f4f6fb;
      margin: 0;
      padding: 40px;
      color: #1a1a1a;
    }

    .container {
      max-width: 560px;
      margin: 0 auto;
      background: #fff;
      padding: 32px 40px;
      border-radius: 18px;
      box-shadow: 0 12px 35px rgba(31, 36, 56, 0.12);
    }

    h1 {
      margin-top: 0;
      font-size: 26px;
    }

    .payment-meta {
      margin-bottom: 24px;
      padding: 16px 20px;
      border-radius: 12px;
      background: #f7f9fe;
      border: 1px solid #ebf0ff;
    }

    .payment-meta dt {
      font-weight: 600;
      margin-bottom: 6px;
    }

    .payment-meta dd {
      margin: 0 0 12px;
      color: #636c8a;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
    }

    select, button, input[type="checkbox"] {
      font-size: 15px;
    }

    select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #d5dcf2;
      appearance: none;
      background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path fill="%239aa0b5" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 14px center;
      background-size: 14px;
    }

    button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, #5b8dff, #4363ff);
      color: #fff;
      padding: 12px 20px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      transition: transform .15s ease, box-shadow .15s ease;
      box-shadow: 0 12px 20px rgba(67, 99, 255, .25);
    }

    button:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 22px rgba(67, 99, 255, .3);
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 16px;
      border: 1px solid transparent;
    }

    .alert--red {
      background: #ffe8ea;
      border-color: #ffccd0;
      color: #a32431;
    }

    .alert--green {
      background: #e6ffef;
      border-color: #b7f7cc;
      color: #1a7a3d;
    }

    .checkbox {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      color: #4b5268;
    }

    .checkbox input {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .help-text {
      font-size: 13px;
      color: #7a8199;
      margin-top: 6px;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Тестовый платеж</h1>

  <?php if ($errors): ?>
    <?php foreach ($errors as $error): ?>
      <?php renderAlert($error, 'alert--red'); ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($successMessage): ?>
    <?php renderAlert($successMessage); ?>
  <?php endif; ?>

  <?php if ($paymentId !== ''): ?>
    <dl class="payment-meta">
      <dt>Номер заказа</dt>
      <dd><?= $orderId !== '' ? htmlspecialchars($orderId) : '—' ?></dd>

      <dt>Переданный payment_id</dt>
      <dd><?= htmlspecialchars($paymentId) ?></dd>

      <dt>Сумма (копейки)</dt>
      <dd><?= $amountCents !== null ? htmlspecialchars((string)$amountCents) : '—' ?></dd>

      <dt>Сумма к оплате</dt>
      <dd><?= $displayAmount !== null ? htmlspecialchars($displayAmount) : '—' ?></dd>

      <dt>URL при успехе</dt>
      <dd><code><?= $successUrl !== '' ? htmlspecialchars($successUrl) : '—' ?></code></dd>

      <dt>URL при отмене</dt>
      <dd><code><?= $canceledUrl !== '' ? htmlspecialchars($canceledUrl) : '—' ?></code></dd>

      <dt>URL при ошибке</dt>
      <dd><code><?= $failedUrl !== '' ? htmlspecialchars($failedUrl) : '—' ?></code></dd>
    </dl>

    <form method="POST" action="">

      <div class="form-group">
        <label for="pay_status">Выберите статус оплаты</label>
        <select name="pay_status" id="pay_status" required>
          <option value="" disabled <?= $selectedStatusLabel ? '' : 'selected' ?>>-- статус --</option>
          <?php foreach ($statuses as $status): ?>
            <option value="<?= htmlspecialchars($status->label) ?>" <?= $status->label === $selectedStatusLabel ? 'selected' : '' ?>>
              <?= htmlspecialchars($status->name) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="help-text">Будет отправлен POST-запрос на ваш /api/webhook/fake как при платёжном шлюзе.</div>
      </div>

      <label class="checkbox">
        <input type="checkbox" name="auto_redirect" value="1" <?= !empty($_POST['auto_redirect']) ? 'checked' : '' ?>>
        После смены статуса перейти на соответствующий URL
      </label>

      <div class="help-text">Например: success_url для "Оплачен", failed_url для "Отказ".</div>

      <button type="submit" name="submit">Отправить вебхук</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
