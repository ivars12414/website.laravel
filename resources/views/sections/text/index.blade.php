@extends('layouts.app')

@section('content')

    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')

            <section class="section section--gray-light news" style="background-image: url(/userfiles/bg.png);">
                <div class="container">
                    <div class="block__header">
                        @include('partials.breadcrumbs')

                        <div class="block__title center">
                            <h1>
                                <strong>{{ $page->meta('h1') ?? $page->meta('title') ?? $page->section()->name }}</strong>
                            </h1>
                        </div>
                    </div>

                    <div class="news__wrapper">
                        @foreach($articles as $article)
                            <div class="news__item">
                                <a href="{{ $article->getUrl() }}" class="news__item-wrapper">
                                    @if(!empty($article->img))
                                        <div class="news__img">
                                            <img src="{{ $article->img }}" alt="{{ $article->title }}">
                                        </div>
                                    @endif

                                    <div class="news__content">
                                        <div class="news__header">
                                            <div class="news__title">{{ $article->title }}</div>
                                            <div class="news__text">{{ strip_tags($article->header) }}</div>
                                        </div>

                                        <div class="news__footer">
                                            @if($article->show_dt)
                                                <div class="date">{{ date('d.m.Y', $article->tm_unix) }}</div>
                                            @endif
                                            <div class="read-more">{!! returnWord('Read more', WORDS_PROJECT) !!}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {!! $articles->links('pagination.articles') !!}

                </div>
            </section>

        </div>

        @include('partials.footer')

    </div>

@endsection
