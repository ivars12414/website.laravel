<?php

namespace App\Cart\Resolvers;

use App\Cart\Contracts\CartItemResolverInterface;
use App\Models\Cart;
use App\Models\CartItem;

class BookingCartItemResolver implements CartItemResolverInterface
{
  public function resolve(string $entityId, int $quantity, array $meta = []): array
  {
    // Эмуляция данных брони
    $mockBookings = [
            1 => ['price' => 500.0, 'image' => 'booking1.jpg', 'discount' => 50.0, 'meta' => ['type' => 'Hotel']],
            2 => ['price' => 1200.0, 'image' => 'booking2.jpg', 'discount' => 150.0, 'meta' => ['type' => 'Flight']],
    ];

    $booking = $mockBookings[$entityId] ?? throw new \InvalidArgumentException('Booking not found.');

    return [
            'price' => $booking['price'],
            'quantity' => $quantity,
            'image' => $booking['image'],
            'discount' => $booking['discount'],
            'meta' => array_merge($booking['meta'], $meta),
            'meta_key' => $this->metaKey($meta),
    ];
  }

  public function metaKey(array $meta): string
  {
    ksort($meta);                     // одинаковый порядок ключей
    return md5(json_encode($meta));   // или sha1 / plain json, если влезает
  }

  public function handleExistingItem(CartItem $existingItem, int $quantity, array $meta = []): CartItem
  {
    // TODO: Implement handleExistingItem() method.
  }

  public function handleAddItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem
  {
    // TODO: Implement handleAddItem() method.
  }

  public function calculatePricing(string $entityId, int $quantity, array $meta = []): array
  {
    // TODO: Implement calculatePricing() method.
  }

  public function handleSetQuantityItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem
  {
    // TODO: Implement handleSetQuantityItem() method.
  }
}
