<?php

namespace App\Cart\Resolvers;

use App\Cart\Contracts\CartItemResolverInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Tax\TaxRateResolver;

class DigitalCartItemResolver implements CartItemResolverInterface
{

  public function handleAddItem(Cart $cart, string $entityId, int $quantity, array $meta = []): CartItem
  {
    return $this->handleSetQuantityItem($cart, $entityId, 1, $meta);
  }

  public function handleSetQuantityItem(
          Cart   $cart,
          string $entityId,
          int    $quantity,      // будет проигнорирован
          array  $meta = []
  ): CartItem
  {
    $data = $this->resolve($entityId, 1, $meta);

    return $cart->items()->updateOrCreate(
            [
                    'type' => 'digital',
                    'entity_id' => $entityId,
                    'meta_key' => $data['meta_key']
            ],
            $data
    );
  }

  public function resolve(string $entityId, int $quantity, array $meta = []): array
  {
    $product = \Website\Inc\ItemsCache::getByHash($entityId, (int)$_SESSION['lang_id'])
            ?? throw new \InvalidArgumentException('Digital product not found.');

    $taxRate = TaxRateResolver::resolve($meta);

    $pricing = $this->calculatePricing($product, $meta);
    $taxAmount = $pricing['total'] * $taxRate;

    return [
            'price' => $pricing['price'],
            'total' => $pricing['total'],
            'quantity' => 1,
            'image' => $product['icon']
                    ? '/userfiles/catalog/' . $product['icon']
                    : '/cms_images/no-img-preview.png',
            'discount' => $pricing['discount'],
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'meta' => array_merge($product['meta'] ?: [], $meta),
            'meta_key' => $this->metaKey($meta),
    ];

  }

  public function metaKey(array $meta): string
  {
    ksort($meta);                     // одинаковый порядок ключей
    return md5(json_encode($meta));   // или sha1 / plain json, если влезает
  }

  public function calculatePricing(array $product, array $meta = []): array
  {

    $price = $product['price'];
    $discount = 0;

    // Изменение цены в зависимости от типа лицензии
    if (isset($meta['license'])) {
      $price *= match ($meta['license']) {
        'commercial' => 2.0,
        'enterprise' => 5.0,
        default => 1.0
      };
    }

    // Пример: скидка для определенного типа лицензии
    if (isset($meta['license']) && $meta['license'] === 'commercial') {
      $discount = $price * 0.05; // 5% скидка на коммерческую лицензию
    }

    return [
            'price' => $price,
            'discount' => $discount,
            'total' => $price - $discount // для цифровых товаров quantity всегда 1
    ];

  }


}
