<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Payment\Handlers\OrderPaymentHandler;
use App\Payment\Handlers\TopUpPaymentHandler;
use App\Payment\Registry\PaymentGatewayRegistry;
use App\Payment\Registry\PaymentHandlerRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PaymentServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap payment gateways and handlers so PaymentService can be used directly.
   */
  public function boot(): void
  {
    $this->registerGateways();
    $this->registerHandlers();
  }

  private function registerGateways(): void
  {
    $methods = PaymentMethod::where('status', 1)
            ->where('deleted', 0)
            ->where('lang_id', lang()->id)
            ->distinct()
            ->pluck('label')
            ->toArray();

    foreach ($methods as $label) {
      $gatewayClass = $this->resolveGatewayClass($label);
      if (class_exists($gatewayClass)) {
        PaymentGatewayRegistry::register($label, $gatewayClass);
      }
    }
  }

  private function registerHandlers(): void
  {
    $handlerMap = [
            Payment::ORDER_TYPE_ORDER => OrderPaymentHandler::class,
            Payment::ORDER_TYPE_TOP_UP => TopUpPaymentHandler::class,
    ];

    foreach ($handlerMap as $type => $handlerClass) {
      PaymentHandlerRegistry::register($type, $handlerClass);
    }
  }

  private function resolveGatewayClass(string $label): string
  {
    return 'App\\Payment\\Gateways\\' . Str::studly($label) . 'Gateway';
  }
}
