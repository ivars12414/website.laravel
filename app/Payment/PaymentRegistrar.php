<?php

namespace App\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Payment\Handlers\OrderPaymentHandler;
use App\Payment\Handlers\TopUpPaymentHandler;
use App\Payment\Registry\PaymentGatewayRegistry;
use App\Payment\Registry\PaymentHandlerRegistry;
use Illuminate\Support\Str;

class PaymentRegistrar
{
  private const GATEWAY_NAMESPACE = 'App\\Payment\\Gateways\\';
  private const GATEWAY_SUFFIX = 'Gateway';

  /**
   * Маппинг типов заказов и их обработчиков
   */
  private const HANDLER_MAP = [
          Payment::ORDER_TYPE_ORDER => OrderPaymentHandler::class,
          Payment::ORDER_TYPE_TOP_UP => TopUpPaymentHandler::class,
  ];

  public static function register(): void
  {
    self::registerGateways();
    self::registerHandlers();
  }

  private static function registerGateways(): void
  {
    // Получаем все активные методы оплаты
    $methods = PaymentMethod::where('status', 1)
            ->where('deleted', 0)
            ->where('lang_id', lang()->id)
            ->distinct()
            ->pluck('label')
            ->toArray();

    foreach ($methods as $label) {
      $gatewayClass = self::resolveGatewayClass($label);
      if (class_exists($gatewayClass)) {
        PaymentGatewayRegistry::register($label, $gatewayClass);
      }
    }
  }

  private static function registerHandlers(): void
  {
    foreach (self::HANDLER_MAP as $type => $handlerClass) {
      PaymentHandlerRegistry::register($type, $handlerClass);
    }
  }

  /**
   * Получить полное имя класса гейтвея на основе label
   */
  public static function resolveGatewayClass(string $label): string
  {
    // Преобразуем snake_case в PascalCase
    $className = Str::studly($label);

    return self::GATEWAY_NAMESPACE . $className . self::GATEWAY_SUFFIX;
  }

  /**
   * Проверить, поддерживается ли метод оплаты
   */
  public static function isGatewaySupported(string $label): bool
  {
    return class_exists(self::resolveGatewayClass($label));
  }
}