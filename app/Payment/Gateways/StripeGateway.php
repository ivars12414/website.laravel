<?php

namespace App\Payment\Gateways;

use App\Models\Payment;
use App\Payment\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    private $stripe;

    public function __construct()
    {
        $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    public function createPayment(array $data): array
    {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $data['currency_code'],
                        'unit_amount' => $data['sum'] * 100,
                        'product_data' => [
                            'name' => "Payment #{$data['payment_id']}"
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'metadata' => [
                    'payment_id' => $data['payment_id'],
                    'operation' => $data['type'],
                ],
            ]);

            return [
                'error' => false,
                'payment_url' => $session->url,
                'gateway_payment_id' => $session->id
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'msg' => $e->getMessage()
            ];
        }
    }

    // Другие методы интерфейса...
    public function processPayment(string $paymentId): array
    {
        // TODO: Implement processPayment() method.
    }

    public function refundPayment(string $paymentId): array
    {
        // TODO: Implement refundPayment() method.
    }
}
