<?php

namespace App\Cart\Services;

use App\Cart\Contracts\CartItemResolverInterface;

class CartItemFactory
{
  /**
   * @var array<string, CartItemResolverInterface>
   */
  protected array $resolvers = [];

  public function __construct(array $resolvers = [])
  {
    $this->resolvers = $resolvers;
  }

  public function registerResolver(string $type, CartItemResolverInterface $resolver): void
  {
    $this->resolvers[$type] = $resolver;
  }

  public function getResolver(string $type): CartItemResolverInterface
  {
    if (!isset($this->resolvers[$type])) {
      throw new \InvalidArgumentException("No resolver registered for type: {$type}");
    }

    return $this->resolvers[$type];
  }

}
