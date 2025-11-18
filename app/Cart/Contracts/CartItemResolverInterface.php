<?php

namespace App\Cart\Contracts;

use App\Models\Cart;
use App\Models\CartItem;

interface CartItemResolverInterface
{
  /**
   * Resolve initial data for a new cart item
   */
  public function resolve(string $entityId, int $quantity, array $meta = []): array;

  /**
   * Handle adding item that might already exist in the cart
   */
  public function handleAddItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem;

  public function handleSetQuantityItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem;

  public function metaKey(array $meta): string;

}
