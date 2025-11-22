@php use App\Services\CreditService; @endphp
@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')
            <section class="section section--gray-light balance" style="background-image: url(/userfiles/bg.png);">
                <div class="container">
                    <div class="block__header">
                        @include('partials.breadcrumbs')

                        <div class="block__title center">
                            <h1><strong>{{ $page->meta('title') }}</strong></h1>
                        </div>
                    </div>

                    <div class="balance__wrapper">
                        <div class="balance__block">
                            <div class="balance__title">{!! returnWord('Available balance', WORDS_PROJECT) !!}</div>

                            <div class="balance__icon">
                                <img src="/images/balance.svg" alt="">
                            </div>
                            <div class="balance__value">{!! CreditService::format(auth()->user()->balance) !!}</div>
                            <a href="#" class="btn btn--main"
                               data-action="credits/top_up"
                               data-cache
                               data-modal-caller>{!! returnWord('Top up', WORDS_PROJECT) !!}</a>
                        </div>

                        <div class="balance__block">
                            <div class="balance__title">{!! returnWord('Orders history', WORDS_PROJECT) !!}</div>

                            @if(empty($balance_log))
                                <div
                                    class="balance__title">{!! returnWord('No orders yet. Click here to top up your wallet.', WORDS_PROJECT) !!}</div>
                            @else
                                <div class="table">
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>{!! returnWord('Order number', WORDS_PROJECT) !!}</th>
                                            <th>{!! returnWord('Date', WORDS_PROJECT) !!}</th>
                                            {{--                  <th>{!! returnWord('Credits', WORDS_PROJECT) !!}</th>--}}
                                            <th>{!! returnWord('Summary', WORDS_PROJECT) !!}</th>
                                            <th>{!! returnWord('Status', WORDS_PROJECT) !!}</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($balance_log as $record)
                                            <tr>
                                                <td data-label="{!! returnWord('Order number', WORDS_PROJECT) !!}">
                                                    {{ $record->id }}
                                                </td>
                                                <td data-label="{!! returnWord('Date', WORDS_PROJECT) !!}">
                                                    <span style="color: #7A7A7A;">{{ $record->date }}</span>
                                                </td>
                                                <td data-label="{!! returnWord('Summary', WORDS_PROJECT) !!}">
                                                    <strong>{{ $record->price->format(true, false) }}</strong>
                                                </td>
                                                <td data-label="{!! returnWord('Status', WORDS_PROJECT) !!}">
                                                    {{ $record->status }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('partials.footer')
    </div>
@endsection

@section('js')
    <script>
        $(function () {

            new FormSubmit({
                formSelector: 'form[data-form=top_up]',
                ajaxUrl: '/credits/top_up',
                successCallback: (r) => {
                    if (r.href) location.href = r.href;
                },
            });

        });
    </script>
@endsection
