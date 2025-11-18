<?php

namespace App\Cart\Services;

use App\Models\Cart;

class CartLocatorService
{
  public function getOrCreateCart(?int $userId, ?string $sessionCode): Cart
  {
    $query = Cart::query()->where('status', Cart::STATUS_DRAFT);
    $cart = null;

    if ($userId) {
      $cart = (clone $query)->where('user_id', $userId)->first();
    }

    if (empty($cart) && $sessionCode) {
      $cart = (clone $query)->where('session_code', $sessionCode)->first();
    }

    if (!$cart) {
      $cart = new Cart([
              'status' => Cart::STATUS_DRAFT,
              'user_id' => (int)$userId,
              'session_code' => $sessionCode,
      ]);
      $cart->save();
    }

    if ($userId && !$cart->user_id) {
      $cart->user_id = $userId;
      $cart->save();
    }

    if (!$cart->session_code && $sessionCode) {
      $cart->session_code = $sessionCode;
      $cart->save();
    }

    return $cart;
  }
}
