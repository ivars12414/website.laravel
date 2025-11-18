<div class="mini-cart__item" data-cart-item-row="{{ $item->id }}">
  <div class="mini-cart__item-img">
    @if(!empty($item->image))
      <img src="{{ $item->image }}" alt="{{ $item->name }}">
    @elseif(!empty($item->image_url))
      <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
    @endif
  </div>
  <div class="mini-cart__item-info">
    <div class="mini-cart__item-title">
      @if(!empty($item->link))
        <a href="{{ $item->link }}">{{ $item->name }}</a>
      @else
        {{ $item->name }}
      @endif
    </div>
    <div class="mini-cart__item-meta">
      <span>{!! returnWord('Qty', WORDS_PROJECT) !!}: {{ $item->quantity }}</span>
      <span>{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($item->total)) !!}</span>
    </div>
    <a href="#" class="mini-cart__remove" data-remove-cart-item="{{ $item->id }}">
      {!! returnWord('Remove', WORDS_PROJECT) !!}
    </a>
  </div>
</div>
