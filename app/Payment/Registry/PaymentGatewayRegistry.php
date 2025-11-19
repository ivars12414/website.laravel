<?php


namespace App\Payment\Registry;

use App\Payment\Contracts\PaymentGatewayInterface;
use App\Payment\Exceptions\PaymentRegistryException;

class PaymentGatewayRegistry
{
  private static array $gateways = [];

  public static function register(string $label, string $gatewayClass): void
  {
    if (!class_exists($gatewayClass)) {
      throw new PaymentRegistryException("Gateway class {$gatewayClass} does not exist");
    }

    if (!is_subclass_of($gatewayClass, PaymentGatewayInterface::class)) {
      throw new PaymentRegistryException("Gateway class must implement PaymentGatewayInterface");
    }

    self::$gateways[$label] = $gatewayClass;
  }

  public static function resolve(string $label): PaymentGatewayInterface
  {
    if (!isset(self::$gateways[$label])) {
      throw new PaymentRegistryException("Unknown payment gateway: {$label}");
    }

    $gatewayClass = self::$gateways[$label];
    return new $gatewayClass();
  }

  public static function getRegisteredGateways(): array
  {
    return array_keys(self::$gateways);
  }
}