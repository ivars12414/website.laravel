@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')
            <div class="container">
                <h1>{{ $page->meta('h1') ?? $page->meta('title') ?? $page->section()->name }}</h1>
                <nav aria-label="breadcrumb">
                    <ol>
                        @foreach($page->breadcrumbs() as $b)
                            <li>
                                @if(!empty($b['url']))
                                    <a href="{{ $b['url'] }}">{{ $b['title'] }}</a>
                                @else
                                    {{ $b['title'] }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                @if($ctx->isItem())
                    <article class="text-section__item">
                        <h2>{{ $ctx->item->title ?? $ctx->item->name ?? $ctx->item->slug }}</h2>
                        <div class="text-section__content">
                            {!! $ctx->item->content ?? $ctx->item->text ?? '' !!}
                        </div>
                    </article>
                @else
                    <div class="text-section__list">
                        @forelse($ctx->items as $item)
                            <article class="text-section__item">
                                <h2>
                                    <a href="{{ $item->getUrl($page->language()->id ?? null) }}">
                                        {{ $item->title ?? $item->name ?? $item->slug }}
                                    </a>
                                </h2>
                                @if(!empty($item->description))
                                    <p>{{ $item->description }}</p>
                                @endif
                            </article>
                        @empty
                            <p>{{ __('No records found') }}</p>
                        @endforelse
                    </div>

                    @if(method_exists($ctx->items, 'links'))
                        <div class="pagination">{!! $ctx->items->links() !!}</div>
                    @endif
                @endif
            </div>
        </div>
        @include('partials.footer')
    </div>
@endsection
