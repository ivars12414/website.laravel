<?php

namespace App\Cart\Services;

use App\Models\Cart;

class CartLocatorService
{
  public function getOrCreateCart(?int $userId, ?string $sessionCode): Cart
  {
    $query = Cart::query()->where('status', Cart::STATUS_DRAFT);

    if ($userId) {
      $query->where('user_id', $userId);
    } elseif ($sessionCode) {
      $query->where('session_code', $sessionCode);
    } else {
      throw new \RuntimeException('Neither user_id nor session_code provided');
    }

    $cart = $query->first();

    if (!$cart) {
      $cart = new Cart([
              'status' => Cart::STATUS_DRAFT,
              'user_id' => (int)$userId,
              'session_code' => $sessionCode,
      ]);
      $cart->save();
    }

    return $cart;
  }
}
