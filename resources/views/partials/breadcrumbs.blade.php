<div class="breadcrumbs center">
    <div class="breadcrumbs__wrapper">
        @foreach($page->breadcrumbs() as $breadcrumb)
            @if(!($firstBreadcrumbShown ?? false))
                <a href="{{ $breadcrumb['url'] }}" class="breadcrumbs__item js-title">
                    <span>{{ $breadcrumb['title'] }}</span>
                </a>
            @else
                <a href="{{ $breadcrumb['url'] }}" class="breadcrumbs__item">
                    {{ $breadcrumb['title'] }}
                </a>
            @endif
            @php $firstBreadcrumbShown = true; @endphp
        @endforeach
    </div>
</div>
