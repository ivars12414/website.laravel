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
                            <a href="{{ sectionHref('catalog') }}"
                               class="btn btn--main">{!! returnWord('Continue shopping', WORDS_PROJECT) !!}</a>
                        </div>
                    @else
                        <div class="cart__wrapper">
                            <div class="products__wrapper" data-cart-items>
                                @foreach($items as $item)
                                    @include('pages.cart.partials.cart_item_in_list', compact('items'))
                                @endforeach
                            </div>

                            @include('pages.cart.partials.promo_code', compact('summary'))

                            <form action="" class="cart__footer" method="POST" data-cart-form data-form="cart">

                                <div class="cart__footer-row js-cart-summary-block">
                                    @include('pages.cart.partials.summary', compact('summary'))
                                </div>

                                <div class="cart__footer-row">
                                    {!! $payment_methods_block !!}

                                    <div class="checkbox">
                                        <input type="checkbox" name="agree" id="checkbox_cart">
                                        <label
                                            for="checkbox_cart">{!! returnWord('agree text', WORDS_PROJECT) !!}</label>
                                    </div>

                                    <button type="submit"
                                            class="btn btn--main">{!! returnWord('Checkout', WORDS_PROJECT) !!}</button>
                                </div>
                            </form>

                        </div>
                    @endif
                </div>
            </section>
        </div>
        @include('partials.footer')
    </div>
@endsection
