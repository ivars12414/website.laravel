@extends('layouts.main')

@section('content')

    <link href="//cart.website.esteriol.dev/resources/css/opened_product.css" rel="stylesheet" type="text/css">

    @include('partials.breadcrumbs')

    <div class="container">
        <div class="product" itemscope itemtype="https://schema.org/Product">

            <div class="product__title">
                <h1 itemprop="name">{{ $item->name }}</h1>
            </div>

            <div class="product__wrapper">

                <div id="product-images"
                     class="product__block no-equipment">

                    <div class="product__slider-wrapper">
                        @if (count($photos) > 1)
                            <div class="product__thumbs-block">
                                <div class=" product__thumbs js-opened-product-thumbs">
                                    @foreach ($photos as $photo)
                                        <div class="">
                                            <img src="{{ $photo['small'] }}" class=""
                                                 alt="{{ $item->name }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="product__images-block @if (count($photos) === 1) no-slider @endif">
                            <div class="product__images js-opened-product-images">
                                @foreach ($photos as $photo)
                                    <div class="">
                                        <div class="product__img">
                                            <a draggable="false" href="{{ $photo['big'] }}" class="fancybox"
                                               rel="BigItems">
                                                <img src="{{ $photo['big'] }}" alt="{{ $item->name }}">
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                <div class="product__info">

                    <div class="product__info-item">

                        <div class="product__price-wrapper">
                            <div class="product__price" id="product-total-price">
                                {{--                                {{ currency($item->price)->format() }}--}}
                            </div>
                        </div>

                        <form class="product__actions" id="add-product-in-cart">
                            <input type="hidden" name="lang_id" value="{{ lang()->id }}">
                            <input type="hidden" name="item_id" value="{{ $item->hash }}">

                            <div class="count__block">

                                <input class="count__input"
                                       type="text"
                                       placeholder="1"
                                       name="quantity"
                                       value="1" data-input>

                                <div>
                                    <a href="#"
                                       class="count__btn plus"
                                       data-action="+">
                                        <span></span>
                                    </a>
                                    <a href="#"
                                       class="count__btn minus"
                                       data-action="-"><span></span></a>
                                </div>
                            </div>

                            <button class="btn btn--green"
                                    type="submit"
                                    id="order_button" data-show-modal="#add_to_cart"><i
                                    class="fas fa-shopping-cart"></i>{!! returnWord('Add to cart', WORDS_INTERFACE) !!}
                            </button>

                        </form>

                        <a class="btn btn--orange btn--full"
                           style="display: none"
                           id="go-to-cart-btn" href="{{ sectionHref('cart') }}">
                            {!! returnWord('Go to cart', WORDS_INTERFACE) !!}
                        </a>

                    </div>

                </div>

            </div>

            <div class="product__tabs">
                <div class="tabs" id="tabs1">
                    <div class="tabs__nav" data-tabs-for="#tabs1" role="tablist">
                        <a class="tabs__nav-item  active" href="#tab-1"
                           role="tab">{!! returnWord('Description', WORDS_INTERFACE) !!}</a>
                    </div>
                    <div class="tabs__content">
                        <div class="tabs__item  active" id="tab-1" role="tabpanel" itemprop="description">
                            {{ $item->descr }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{--        <script type="application/ld+json">--}}
        {{--            {!! json_encode([--}}
        {{--                '@context' => 'https://schema.org',--}}
        {{--                '@type' => 'Product',--}}
        {{--                'description' => $item->descr,--}}
        {{--                'name' => $item->name,--}}
        {{--                'image' => 'https://' . getenv('HTTP_HOST') . $item->imgUrl,--}}
        {{--                'offers' => [--}}
        {{--                    '@type' => 'Offer',--}}
        {{--                    'price' => $item->price,--}}
        {{--                    'priceCurrency' => 'EUR'--}}
        {{--                ]--}}
        {{--            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}--}}
        {{--        </script>--}}

        @include('pages.catalog.partials.similar')

    </div>


    <script src="//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js"></script>
    {{--    <script src="/resources/js/product.js?v=1.<?= time() ?>"></script>--}}

@endsection
