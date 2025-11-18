@if($items->isEmpty())
  Cart is empty
@else
  <div class="mini-cart__container">
    <div class="products__wrapper">
      @foreach($items as $item)
        @include('pages.cart.partials.cart_item_in_list')
      @endforeach
    </div>

    <div class="cart__footer">
      <div class="cart__footer-row">

        <div class="cart__value">{!! returnWord('Totally', WORDS_PROJECT) !!}:</div>
        <div class="cart__value">{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($summary['total'])) !!}</div>
      </div>

      <div class="cart__footer-row">
        {{--        <div class="checkbox">--}}
        {{--          <input type="checkbox" name="checkbox_cart" id="checkbox_cart">--}}
        {{--          <label for="checkbox_cart"><p>I have read and agree the&nbsp;<a href="privacy.html">Privacy--}}
        {{--                policy</a>.</p></label>--}}
        {{--        </div>--}}

        <a href="{{ sectionHref('cart') }}" class="btn btn--main">{!! returnWord('Checkout', WORDS_PROJECT) !!}</a>
      </div>
    </div>
  </div>

  <script>
    $(function () {
      $('[name="quantity"]').inputMask({type: 'number', numberMin: 1});
    })
  </script>
@endif