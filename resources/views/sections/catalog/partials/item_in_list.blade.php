<div class="product__item">
    <div class="product__item-wrapper">
        <div class="product__item-header">
            <div class="product__img">
                <img src="{{ $item->imgUrl }}" alt="{{ $item->name }}">
            </div>

            <div class="product__title">{{ $item->name }}</div>
            <div class="product__period">
                {{--                @if($item->volume > 0)--}}
                {{--                    {{ \Illuminate\Support\Number::fileSize(bytes: $item->volume, maxPrecision: 0) }} <span>/</span>--}}
                {{--                @endif--}}
                {{--                @if($item->duration > 0)--}}
                {{--                    {{ $item->duration }} {!! returnWord('days', WORDS_INTERFACE) !!}--}}
                {{--                @endif--}}
            </div>
            <div class="product__text">{!! $item->description !!}</div>
        </div>

        <form class="product__item-footer" action="/api/cart/add-product" method="post" data-add-to-cart>
            <input type="hidden" name="item_id" value="{{ $item->id }}">
            <input type="hidden" name="quantity" value="1">

            {{--      <div class="product__price">{!! currency($item->price)->format() !!}</div>--}}
            {{--      <div class="product__price">{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($item->price)) !!}</div>--}}

            {{--      @if(isLoged())--}}
            {{--        <button class="btn btn--main" type="submit">--}}
            {{--          {!! returnWord('Buy now', WORDS_INTERFACE) !!}--}}
            {{--        </button>--}}

            {{--        <a class="btn btn--main"--}}
            {{--           style="display: none"--}}
            {{--           data-go-to-cart-btn href="{{ sectionHref('cart') }}">--}}
            {{--          {!! returnWord('Go to cart', WORDS_INTERFACE) !!}--}}
            {{--        </a>--}}
            {{--      @else--}}
            <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login" data-cache
               data-text="{!! returnWord('Please login to make purchases', WORDS_PROJECT) !!}">
                {!! returnWord('Buy now', WORDS_INTERFACE) !!}
            </a>
            {{--      @endif--}}


        </form>
    </div>
</div>
