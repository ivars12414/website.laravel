<div class="countries__item"{!! $i > 11 ? ' style="display: none;"' : '' !!}>
  <a href="{{ $category->link }}" class="countries__item-wrapper">
    <div class="countries__flag">
      <img src="{{ $category->img_url }}" alt="{{ $category->name }}">
    </div>

    <div class="countries__content">
      <div class="countries__name">{{ $category->name }}</div>
    </div>
  </a>
</div>