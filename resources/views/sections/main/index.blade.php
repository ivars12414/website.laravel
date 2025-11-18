@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')

            <section class="intro">
                <div class="animate js-animate">
                    <img src="/userfiles/intro.svg" alt="">
                </div>

                <div class="container">
                    <div class="intro__wrapper">
                        <div class="intro__content">
                            <div class="intro__subtitle js-title">
                                <span>{!! returnWord('main intro subtitle', WORDS_PROJECT) !!}</span>
                            </div>
                            <div class="intro__title">{!! returnWord('main intro title', WORDS_PROJECT) !!}</div>
                            <div class="intro__text">{!! returnWord('main intro text', WORDS_PROJECT) !!}</div>
                            <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login"
                               data-cache>{!! returnWord('main intro button text', WORDS_PROJECT) !!}</a>
                        </div>
                    </div>
                </div>
            </section>

            @if(!$main_categories->isEmpty())
                <!-- countries -->
                <section class="section section--gray-light countries"
                         style="background-image: url(/userfiles/bg.png);">
                    <div class="container">
                        <div class="block__header">
                            <div class="block__subtitle center js-title">
                                <span>{!! returnWord('main countries subtitle', WORDS_PROJECT) !!}</span></div>

                            <div class="block__title center js-title">
                                <h2>{!! returnWord('main countries title', WORDS_PROJECT) !!}</h2>
                            </div>

                            <div
                                class="block__text center">{!! returnWord('main countries text', WORDS_PROJECT) !!}</div>
                        </div>

                        <div class="countries__wrapper">
                            @foreach($main_categories as $category)
                                <div class="countries__item">
                                    <a href="{{ $category->link }}" class="countries__item-wrapper">
                                        <div class="countries__flag">
                                            <img src="{{ $category->imgUrl }}"
                                                 alt="{{ $category->name }} plans from">
                                        </div>

                                        <div class="countries__content">
                                            <div class="countries__name">{{ $category->name }}</div>
                                            <span
                                                class="btn btn--o-main">{!! returnWord('from %PRICE%', WORDS_INTERFACE, ['%PRICE%' => currency($category->min_price)->convert()->format(withStrongInt: false)]) !!}</span>
                                            <span class="btn btn--o-main"></span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach

                        </div>

                        <div class="actions center">
                            <a href="{{ sectionHref('catalog') }}"
                               class="btn btn--main">{!! returnWord('View all countries', WORDS_PROJECT) !!}</a>
                        </div>
                    </div>
                </section>
                <!-- /countries -->
            @endif

        </div>
        @include('partials.footer')
    </div>
@endsection
