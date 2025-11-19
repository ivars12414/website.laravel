<div class="product__item" data-cart-item-row="{{ $item->id }}">
    <div class="product__item-wrapper">
        <div class="product__item-header">
            <div class="product__img">
                <img src="{{ $item->imageUrl }}" alt="{{ $item->name }}">
            </div>

            <div class="product__title">{{ $item->name }}</div>
            <div class="product__text">{!! $item->description !!}</div>
        </div>

        <div class="product__item-footer">
            <div
                class="product__price">{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($item->price)) !!}</div>

            <form class="product__count js-cart-item-count-form">
                <input type="hidden" name="item_id" value="{{ $item->entity_id }}">
                <input type="hidden" name="meta" value="{{ htmlspecialchars(json_encode($item->meta)) }}">

                <div class="count__wrapper">
                    <a href="#"
                       class="btn btn--main minus"
                       data-action="-"></a>

                    <input value="{{ $item->quantity }}"
                           name="quantity"
                           placeholder="1"
                           class="input"
                           type="text"
                           autocomplete="off"
                           data-input>

                    <a href="#" class="btn btn--main plus"
                       data-action="+"></a>
                </div>
            </form>

            <button class="product__delete" data-remove-cart-item="{{ $item->id }}">
                <img src="/images/delete.svg" alt="{!! returnWord('Remove item from cart icon', WORDS_PROJECT) !!}">
            </button>
        </div>
    </div>
</div>
