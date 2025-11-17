@if (!empty($similarItems))
  <div class="block__title">
    <h2>{!! returnWord("Similar offers", WORDS_INTERFACE) !!}</h2>
  </div>
  <div class="products products-block similar">
    <div class="products__similar-slider js-main-product-slider">
      @foreach($similarItems as $item)
        @include('pages.catalog.partials.item_in_list')
      @endforeach
    </div>
  </div>
@endif