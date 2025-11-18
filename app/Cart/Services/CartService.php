<?php

namespace App\Cart\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Status;
use App\Services\Currency\CurrencyManager;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;

class CartService
{
    public function __construct(
        protected CartItemFactory $factory
    )
    {
    }

    public function addItem(Cart $cart, string $type, string $entityId, int $quantity, array $meta = []): CartItem
    {
        $resolver = $this->factory->getResolver($type);
        return $resolver->handleAddItem($cart, $entityId, $quantity, $meta);
    }

    public function setItemQuantity(Cart $cart, string $type, string $entityId, int $quantity, array $meta = []): CartItem
    {
        $resolver = $this->factory->getResolver($type);
        return $resolver->handleSetQuantityItem($cart, $entityId, $quantity, $meta);
    }

    public function removeItem(Cart $cart, int $itemId): void
    {
        $item = $cart->items()->findOrFail($itemId);
        $item->delete();
    }

    public function applyPromoCode(Cart $cart, string $code): bool
    {
        /** @var PromoCode $promo */
        $promo = PromoCode::findByCode($code);
        if (!$promo) {
            return false;
        }

        return $promo->applyToCart($cart);
    }

    public function removePromoCode(Cart $cart): bool
    {
        /** @var PromoCode $promo */
        $promo = $cart->promoCode;
        if (!$promo) {
            return false;
        }

        $promo->removeFromCart($cart);
        return true;
    }

    public function recalculateDiscounts(Cart $cart): void
    {
        if ($cart->promoCode) {
            $cart->promoCode->applyToCart($cart);
        }
    }

    public function freeze(Cart $cart): Order
    {

        return DB::transaction(function () use ($cart) {

            // 1. Берём свежий экземпляр под lock
            /** @var Cart $cart */
            $cart = Cart::whereKey($cart->id)
                ->where('status', Cart::STATUS_DRAFT)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Создаём новый Order и копируем нужные поля вручную
            $order = new Order();

            // забираем все атрибуты корзины
            $attrs = $cart->getAttributes();

            // убираем поля, которые не должны попасть в заказ как есть
            unset(
                $attrs['id'],
                $attrs['created_at'],
                $attrs['updated_at'],
                $attrs['session_code'],
                $attrs['status']
            );

            // накидываем оставшееся в заказ
            $order->fill($attrs);

            // добавляем то, чего не было в Cart, но должно быть в Order
            $order->cart_id = $cart->id;
            $order->mail = auth('client')->user()->mail ?? '';
            $order->name = auth('client')->user()->name ?? '';
            $order->surname = auth('client')->user()->surname ?? '';

            $currentCur = CurrencyManager::current();
            $order->currency_code = $currentCur->code;
            $order->currency_rate = $currentCur->value > 0 ? $currentCur->value : 1;

            $order->save();

            // 3. Позиции копируем через replicate() пачками
            CartItem::where('cart_id', $cart->id)
                ->chunkById(500, function ($items) use ($order) {

                    $bulkInsert = [];

                    foreach ($items as $item) {
                        // Берём чистые атрибуты корзинного айтема
                        $data = $item->getAttributes();

                        // Удаляем всё, что в order_items быть не должно
                        unset(
                            $data['id'],          // пусть order_items сам сгенерит свой id
                            $data['cart_id'],     // в заказе вместо этого будет order_id
                            $data['created_at'],  // при желании можно оставить, см. ниже
                            $data['updated_at']   // то же самое
                        );

                        // Добавляем обязательные для ордера поля
                        $data['order_id'] = $order->id;
                        $data['created_at'] = Carbon::now();

                        $bulkInsert[] = $data;
                    }

                    if (!empty($bulkInsert)) {
                        DB::table('order_items')->insert($bulkInsert);
                    }
                });

            // 4. Обновляем статус корзины (или удаляем, если нужно)
//      $cart->freeze();

            $status = Status::findByLabel('created', 'orders');
            changeOrderStatus($order->id, $status->id, SOURCE_SITE);

            return $order;
        });
    }
}
