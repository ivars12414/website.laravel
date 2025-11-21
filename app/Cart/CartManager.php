<?php

namespace App\Cart;

use App\Cart\Factories\CartServiceFactory;
use App\Cart\Services\CartLocatorService;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartManager
{
  protected static ?Cart $cart = null;

  protected static function getCart(?int $userId, ?string $sessionCode): Cart
  {
    $userId = self::resolveUserId($userId);
    $sessionCode = self::resolveSessionCode($sessionCode);

    if (!self::$cart
        || ($userId && self::$cart->user_id !== $userId)
        || (!$userId && self::$cart->session_code !== $sessionCode)) {
      $locator = new CartLocatorService();
      self::$cart = $locator->getOrCreateCart($userId, $sessionCode);
    }

    return self::$cart;
  }

  protected static function resolveUserId(?int $userId): ?int
  {
    if ($userId) {
      return $userId;
    }

    if (Auth::check()) {
      return (int)Auth::id();
    }

    return null;
  }

  protected static function resolveSessionCode(?string $sessionCode): string
  {
    if (!empty($sessionCode)) {
      return $sessionCode;
    }

    $existingSessionCode = session('session_code') ?? ($_SESSION['session_code'] ?? '');

    if (!empty($existingSessionCode)) {
      return (string)$existingSessionCode;
    }

    $generated = (string)Str::uuid();
    session(['session_code' => $generated]);
    $_SESSION['session_code'] = $generated;

    return $generated;
  }

  public static function addItem(
          string  $type,
          string  $entityId,
          int     $quantity,
          array   $meta = [],
          ?int    $userId = 0,
          ?string $sessionCode = ''
  ): CartItem
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    $cartItem = $service->addItem($cart, $type, $entityId, $quantity, $meta);

    $service->recalculateDiscounts($cart);

    return $cartItem;
  }

  public static function setItemQuantity(string  $type,
                                         string  $entityId,
                                         int     $quantity,
                                         array   $meta = [],
                                         ?int    $userId = 0,
                                         ?string $sessionCode = ''): CartItem
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    $cartItem = $service->setItemQuantity($cart, $type, $entityId, $quantity, $meta);

    $service->recalculateDiscounts($cart);

    return $cartItem;
  }

  public static function removeItem(
          int     $itemId,
          ?int    $userId = null,
          ?string $sessionCode = null
  ): void
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    $service->removeItem($cart, $itemId);
    $service->recalculateDiscounts($cart);
  }

  public static function applyPromo(string $code, ?int $userId = null, ?string $sessionCode = null): bool
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    return $service->applyPromoCode($cart, $code);
  }

  public static function recalculateDiscounts(
          ?int    $userId = null,
          ?string $sessionCode = null
  ): void
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();
    $service->recalculateDiscounts($cart);
  }

  public static function removePromo(?int $userId = null, ?string $sessionCode = null): bool
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    return $service->removePromoCode($cart);
  }

  public static function freeze(?int $userId = null, ?string $sessionCode = null): Order
  {
    $cart = self::getCart($userId, $sessionCode);
    $service = CartServiceFactory::create();

    return $service->freeze($cart);
  }

  public static function setCartFrozen(?int $userId = null, ?string $sessionCode = null): void
  {
    $cart = self::getCart($userId, $sessionCode);
    $cart->freeze();
  }

  public static function getTotal(?int $userId = null, ?string $sessionCode = null): float
  {
    return self::getCart($userId, $sessionCode)->getTotal();
  }

  public static function getSummary(?int $userId = null, ?string $sessionCode = null): array
  {
    $cart = self::getCart($userId, $sessionCode);

    return [
            'subtotal' => $cart->getSubtotal(),
            'discount' => $cart->getDiscountTotal(),
            'tax' => $cart->getTax(),
            'total' => $cart->getTotal(),
            'promo_code' => $cart->promoCode,
            'qty' => $cart->items->sum('quantity'),
    ];
  }

  public static function getItems(?int $userId = null, ?string $sessionCode = null): iterable
  {
    return self::getCart($userId, $sessionCode)->items;
  }

  public static function clearCart(?int $userId = null, ?string $sessionCode = null): void
  {
    $cart = self::getCart($userId, $sessionCode);
    $cart->items()->delete();
  }

  public static function getCartPromo(?int $userId = null, ?string $sessionCode = null): ?PromoCode
  {
    $cart = self::getCart($userId, $sessionCode);
    return $cart->promoCode;
  }


}
