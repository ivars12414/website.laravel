@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')

            @if (!$ctx->categories->isEmpty())
                <section class="section section--gray-light countries"
                         style="background-image: url(/userfiles/bg.png);">
                    <div class="container">
                        <div class="block__header">
                            @include('partials.breadcrumbs')

                            <div class="block__title center js-title">
                                <h1><?= $currentCategory->name ?? section()->getH1() ?></h1>
                            </div>
                        </div>

                        <div class="countries__wrapper" id="categories-list">
                            @php $catsI = 0; @endphp
                            @foreach($ctx->categories as $category)
                                @include('sections.catalog.partials.category_in_list', ['category' => $category, 'i' => $catsI++])
                            @endforeach
                        </div>

                        <div class="actions center">
                            <a href="#" class="btn btn--o-main"
                               data-view-all="#categories-list">{!! returnWord('View all', WORDS_PROJECT) !!}</a>
                        </div>
                    </div>
                </section>
            @endif

            @if (!$ctx->items->isEmpty())
                <section class="section section--gray-light products" style="background-image: url(/userfiles/bg.png);">
                    <div class="container">
                        <div class="block__header">
                            @include('partials.breadcrumbs')

                            <div class="block__title center">
                                <h1><strong>{!! $ctx->category->name ?? section()->getH1() !!}</strong></h1>
                            </div>
                        </div>

                        @if(isConfig('catalog_filter_enabled'))
                            @include('sections.catalog.partials.filter', [
                                'items' => $ctx->items,
                                'filters' => $ctx->filters,
                                'currentCategory' => $ctx->category,
                            ])
                        @endif

                        <div data-items-list>
                            @include('sections.catalog.partials.items_list', ['items' => $ctx->items])
                        </div>
                        <div data-items-pagination>
                            {{ $ctx->items->links() }}
                        </div>
                    </div>
                </section>
            @endif

            {{--            @include('partials.faq')--}}
        </div>
        @include('partials.footer')
    </div>
@endsection

@section('js')
    {{--    <script src="//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js"></script>--}}
    {{--    <script src="/resources/js/product.js?v=1.<?= time() ?>"></script>--}}
    {{--    <script>--}}
    {{--        $(function () {--}}
    {{--            $('[data-view-all]').on('click', function (e) {--}}
    {{--                e.preventDefault();--}}
    {{--                const target = $(this).data('viewAll');--}}
    {{--                $(target).children().show();--}}
    {{--                $(this).remove();--}}
    {{--            });--}}

    {{--            new FormSubmit({--}}
    {{--                formSelector: 'form[data-form=filter]',--}}
    {{--                ajaxUrl: '/api/catalog/filter',--}}
    {{--                beforeSendCallback: (fs, {$form}) => {--}}
    {{--                    $form.addClass('loading-stripe');--}}
    {{--                    $('[data-items-list], [data-items-pagination]').addClass('loading-stripe');--}}
    {{--                },--}}
    {{--                afterSendCallback: (fs, {$form}) => {--}}
    {{--                    $form.removeClass('loading-stripe');--}}
    {{--                    $('[data-items-list], [data-items-pagination]').removeClass('loading-stripe');--}}
    {{--                },--}}
    {{--                successCallback: (r, {$form}) => {--}}
    {{--                    $('[data-items-list]').html(r.items);--}}
    {{--                    $('[data-items-pagination]').html(r.pagination);--}}
    {{--                    window.history.pushState({}, document.getElementsByTagName('title')[0].innerHTML, '?' + r.query);--}}
    {{--                    Scroll.scrollToElement($form, 200, 700);--}}
    {{--                },--}}
    {{--            });--}}

    {{--            $('body').on('click', '[data-page-link]', function (e) {--}}
    {{--                e.preventDefault();--}}
    {{--                $('[data-page-input]').val($(this).data('pageLink'));--}}
    {{--                $('form[data-form=filter]').trigger('submit');--}}
    {{--            });--}}
    {{--        })--}}
    {{--    </script>--}}
@endsection

