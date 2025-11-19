<?php


namespace App\Payment\Registry;

use App\Payment\Contracts\PaymentEntityHandlerInterface;
use App\Payment\Exceptions\PaymentRegistryException;

class PaymentHandlerRegistry
{
  private static array $handlers = [];

  public static function register(string $type, string $handlerClass): void
  {
    if (!class_exists($handlerClass)) {
      throw new PaymentRegistryException("Handler class {$handlerClass} does not exist");
    }

    if (!is_subclass_of($handlerClass, PaymentEntityHandlerInterface::class)) {
      throw new PaymentRegistryException("Handler class must implement PaymentEntityHandlerInterface");
    }

    self::$handlers[$type] = $handlerClass;
  }

  public static function resolve(string $type): ?PaymentEntityHandlerInterface
  {
    if (!isset(self::$handlers[$type])) {
      return null;
    }

    $handlerClass = self::$handlers[$type];
    return new $handlerClass();
  }

  public static function getRegisteredHandlers(): array
  {
    return array_keys(self::$handlers);
  }
}