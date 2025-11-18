<?php

namespace App\Cart\Resolvers;

use App\Cart\Contracts\CartItemResolverInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;

//use App\Tax\TaxRateResolver;

class ProductCartItemResolver implements CartItemResolverInterface
{

  public function handleAddItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem
  {
    // текущее количество (0, если позиции ещё нет)
    $currentQty = (int)$cart->items()
            ->where('type', 'product')
            ->where('entity_id', $entityId)
            ->where('meta_key', $this->metaKey($meta))
            ->value('quantity');

    return $this->handleSetQuantityItem($cart, $entityId, $currentQty + $quantity, $meta);
  }

  public function handleSetQuantityItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem
  {
    $data = $this->resolve($entityId, $quantity, $meta);

    return $cart->items()->updateOrCreate(
            [
                    'type' => 'product',
                    'entity_id' => $entityId,
                    'meta_key' => $data['meta_key']
            ],
            $data
    );
  }

  public function resolve(string $entityId, int $quantity, array $meta = []): array
  {

    $product = Item::findOrFail($entityId);

    $pricing = $this->calculatePricing($product, $quantity, $meta);

//    $taxRate = TaxRateResolver::resolve($meta);
//    $taxAmount = $pricing['total'] * $taxRate;

    return [
            'price' => $pricing['price'],
            'discount' => $pricing['discount'],
            'total' => $pricing['total'],
            'quantity' => $quantity,
            'image' => $product->imgUrl,
            'meta' => $meta,
            'meta_key' => $this->metaKey($meta),
//            'tax_rate' => $taxRate,
//            'tax_amount' => $taxAmount,
    ];

  }

  public function metaKey(array $meta): string
  {
    ksort($meta);                     // одинаковый порядок ключей
    return md5(json_encode($meta));   // или sha1 / plain json, если влезает
  }

  public function calculatePricing($product, int $quantity, array $meta = []): array
  {

    $price = $product->price;
    $discount = 0;

    // Пример: оптовая скидка при большом количестве
    if ($quantity >= 10) {
      $discount = $price * 0.1; // 10% скидка
    }

    $subtotal = $price * $quantity;
    $totalDiscount = $discount * $quantity;

    // вот уже тут можно посчитать цену с НДС и без
    return [
            'price' => $price,
            'discount' => $discount,
            'total' => $subtotal - $totalDiscount
    ];
  }
}
