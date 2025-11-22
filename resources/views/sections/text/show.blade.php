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
                            <h1><strong>{{ $article->title }}</strong></h1>
                        </div>

                        @if($article->show_dt)
                            <div class="date center">{{ date('d.m.Y', $article->tm_unix) }}</div>
                        @endif
                    </div>

                    @if(!empty($article->img))
                        <div class="img">
                            <img src="{{ $article->img }}" alt="{{ $article->title }}">
                        </div>
                    @endif

                    <div class="block__text">
                        {!! $article->content !!}
                    </div>

                    @if(!empty($shareLinks))
                        <div class="share">
                            <div class="share__wrapper">
                                <div
                                    class="share__title">{!! returnWord('Do you like this article? Share it on:', WORDS_PROJECT) !!}</div>

                                <div class="share__list">
                                    @foreach($shareLinks as $shareLink)
                                        <a href="{{ $shareLink['href'] }}" class="share__item">
                                            @if(!empty($shareLink['img']))
                                                <img src="{{ $shareLink['img'] }}" alt="{{ $shareLink['alt'] }}">
                                            @else
                                                {{ $shareLink['name'] }}
                                            @endif
                                        </a>
                                    @endforeach
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
