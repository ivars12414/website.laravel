<?php

namespace App\Payment\Contracts;

interface PaymentGatewayInterface
{
  public function createPayment(array $data): array;

  public function processPayment(string $paymentId): array;

  public function refundPayment(string $paymentId): array;
}