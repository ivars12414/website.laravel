@if($items->isEmpty())
    <div class="block__title center">
        {!! returnWord('Nothing found', WORDS_PROJECT) !!}
    </div>
@else
    <div class="products__wrapper">
        @foreach($items as $item)
            @include('sections.catalog.partials.item_in_list')
        @endforeach
    </div>
@endif
