@if ($paginator->hasPages())
    <div class="paginations">
        <div class="paginations__wrapper">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="pagination__item pagination__item--left pagination__item--disabled">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="pagination__item pagination__item--left">Previous</a>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination__item">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination__item pagination__item--current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination__item">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination__item pagination__item--right">Next</a>
            @else
                <span class="pagination__item pagination__item--right pagination__item--disabled">Next</span>
            @endif

        </div>
    </div>
@endif
