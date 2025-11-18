@extends('layouts.app')

@section('content')
  <div class="page">
    <div class="page__wrapper">
      @include('partials.header')

      <section class="section section--gray-light cart" style="background-image: url(/userfiles/bg.png);">
        <div class="container">
          <div class="block__header">
            @include('partials.breadcrumbs')

            <div class="block__title center">
              <h1><strong>{{ $page->meta('h1') ?? $page->section()->getH1() }}</strong></h1>
            </div>
          </div>

          @if($items->isEmpty())
            <div class="cart__empty center">
              <p>{!! returnWord('Cart is empty', WORDS_PROJECT) !!}</p>
              <a href="{{ sectionHref('catalog') }}" class="btn btn--main">{!! returnWord('Continue shopping', WORDS_PROJECT) !!}</a>
            </div>
          @else
            <div class="cart__wrapper">
              <div class="products__wrapper" data-cart-items>
                @foreach($items as $item)
                  <div class="product__item" data-cart-item-row="{{ $item->id }}">
                    <div class="product__item-wrapper">
                      <div class="product__item-header">
                        <div class="product__img">
                          @if(!empty($item->image))
                            <img src="{{ $item->image }}" alt="{{ $item->name }}">
                          @elseif(!empty($item->image_url))
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                          @endif
                        </div>

                        <div class="product__title">{{ $item->name }}</div>
                        <div class="product__period">
                          @if($item->volume > 0)
                            {{ \Illuminate\Support\Number::fileSize(bytes: $item->volume ?? 0, maxPrecision: 0) }} <span>/</span>
                          @endif
                          @if($item->duration > 0)
                            {{ $item->duration }} {!! returnWord('days', WORDS_INTERFACE) !!}
                          @endif
                        </div>
                        <div class="product__text">{!! $item->description !!}</div>
                      </div>

                      <div class="product__item-footer">
                        <div class="product__price">
                          {!! \App\Services\CreditService::format(\App\Services\CreditService::convert($item->total)) !!}
                        </div>

                        <form class="product__count js-cart-item-count-form">
                          <input type="hidden" name="item_id" value="{{ $item->entity_id }}">

                          <div class="count__wrapper">
                            <a href="#" class="btn btn--main minus" data-action="-"></a>

                            <input value="{{ $item->quantity }}"
                                   name="quantity"
                                   placeholder="1"
                                   class="input"
                                   type="text"
                                   autocomplete="off"
                                   data-input>

                            <a href="#" class="btn btn--main plus" data-action="+"></a>
                          </div>
                        </form>

                        <button class="product__delete" data-remove-cart-item="{{ $item->id }}">
                          <img src="/images/delete.svg" alt="{!! returnWord('Remove item from cart icon', WORDS_PROJECT) !!}">
                        </button>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="cart__footer">
                <div class="cart__footer-row js-cart-summary-block">
                  @include('pages.cart.partials.summary', compact('summary'))
                </div>
              </div>
            </div>
          @endif
        </div>
      </section>
    </div>
    @include('partials.footer')
  </div>
@endsection
