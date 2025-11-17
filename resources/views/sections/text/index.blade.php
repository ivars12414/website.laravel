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
                <p>Text section stub.</p>
            </div>
        </div>
        @include('partials.footer')
    </div>
@endsection
