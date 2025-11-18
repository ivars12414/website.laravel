<?php

namespace App\Cart\Factories;

use App\Cart\Resolvers\BookingCartItemResolver;
use App\Cart\Resolvers\DigitalCartItemResolver;
use App\Cart\Resolvers\ProductCartItemResolver;
use App\Cart\Services\CartItemFactory;
use App\Cart\Services\CartService;

class CartServiceFactory
{
  public static function create(): CartService
  {
    $factory = new CartItemFactory();

    $factory->registerResolver('product', new ProductCartItemResolver());
//    $factory->registerResolver('digital', new DigitalCartItemResolver());
//    $factory->registerResolver('booking', new BookingCartItemResolver());

    return new CartService($factory);
  }
}
