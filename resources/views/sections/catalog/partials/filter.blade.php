@php
    $durations = collect($filterOptions['durations'] ?? [])->filter();
    $volumes = collect($filterOptions['volumes'] ?? [])->filter();
    $dataTypes = collect($filterOptions['dataTypes'] ?? [])->filter();
@endphp

<form method="get" action="{{ url()->current() }}" class="catalog-filter" data-form="filter">
    <div class="catalog-filter__grid">
        <div class="catalog-filter__field">
            <label class="label">{!! returnWord('Duration', WORDS_INTERFACE) !!}</label>
            <select name="duration" class="js-select" data-placeholder="{!! returnWord('Any', WORDS_INTERFACE) !!}">
                <option value="">{!! returnWord('Any', WORDS_INTERFACE) !!}</option>
                @foreach($durations as $duration)
                    <option value="{{ $duration }}" @selected(($filters['duration'] ?? '') == $duration)>
                        {{ $duration }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="catalog-filter__field">
            <label class="label">{!! returnWord('Volume', WORDS_INTERFACE) !!}</label>
            <select name="volume" class="js-select" data-placeholder="{!! returnWord('Any', WORDS_INTERFACE) !!}">
                <option value="">{!! returnWord('Any', WORDS_INTERFACE) !!}</option>
                @foreach($volumes as $volume)
                    <option value="{{ $volume }}" @selected(($filters['volume'] ?? '') == $volume)>
                        {{ $volume }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="catalog-filter__field">
            <label class="label">{!! returnWord('Data type', WORDS_INTERFACE) !!}</label>
            <select name="data_type" class="js-select" data-placeholder="{!! returnWord('Any', WORDS_INTERFACE) !!}">
                <option value="">{!! returnWord('Any', WORDS_INTERFACE) !!}</option>
                @foreach($dataTypes as $dataType)
                    <option value="{{ $dataType }}" @selected(($filters['data_type'] ?? '') == $dataType)>
                        {{ $dataType }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="catalog-filter__field">
            <label class="label">{!! returnWord('Search', WORDS_INTERFACE) !!}</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{!! returnWord('Search', WORDS_INTERFACE) !!}">
        </div>
    </div>

    <div class="catalog-filter__actions">
        <button type="submit" class="btn btn--main">{!! returnWord('Apply', WORDS_INTERFACE) !!}</button>
        <a href="{{ url()->current() }}" class="btn btn--o-main">{!! returnWord('Reset', WORDS_INTERFACE) !!}</a>
    </div>
</form>
