<form class="filter__form" data-form="filter">
    <input type="hidden" name="category" value="{{ $currentCategory->slug }}">
    <input data-page-input type="hidden" name="page" value="{{ $_GET['page'] ??'' }}">

    <div class="filter__item">
        <label for="filter_name" class="label">{!! returnWord('Name', WORDS_PROJECT) !!}</label>
        <input type="text" class="input" id="filter_name" name="search" value="{{ $ctx->filters['search'] ?? '' }}">
    </div>

    <div class="filter__item">
        <label for="filter_duration" class="label">{!! returnWord('Duration (days)', WORDS_PROJECT) !!}</label>
        <div class="select">
            <select name="duration" id="filter_duration">
                <option value="">{!! returnWord('All', WORDS_INTERFACE) !!}</option>
                @foreach($filterOptions['durations'] as $duration)
                    <option
                        value="{{ $duration }}" @selected(isset($ctx->filters['duration']) && $duration == $ctx->filters['duration'])>{{ $duration }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="filter__item">
        <label for="filter_data" class="label">{!! returnWord('Data', WORDS_PROJECT) !!}</label>
        <div class="select">
            <select name="volume" id="filter_data">
                <option value="">{!! returnWord('All', WORDS_INTERFACE) !!}</option>
                @foreach($filterOptions['volumes'] as $volume)
                    <option
                        value="{{ $volume }}"
                        @selected(isset($ctx->filters['volume']) && $volume == $ctx->filters['volume'])>
                        {{ \Illuminate\Support\Number::fileSize(bytes: $volume ?? 0, maxPrecision: 0) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="filter__item">
        <label for="filter_data_type" class="label">{!! returnWord('Data type', WORDS_PROJECT) !!}</label>
        <div class="select">
            <select name="data_type" id="filter_data_type">
                <option value="">{!! returnWord('All', WORDS_INTERFACE) !!}</option>
                @foreach($filterOptions['dataTypes'] as $data_type)
                    <option
                        value="{{ $data_type }}" @selected(isset($ctx->filters['data_type']) && $data_type == $ctx->filters['data_type'])>{!! returnWord(DATA_TYPES_MAPPING[$data_type] ?? "Data type $data_type", WORDS_PROJECT) !!}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="filter__item actions">
        <button class="btn btn--main" type="submit"
                data-page-link="1">{!! returnWord('Filter', WORDS_PROJECT) !!}</button>
        <a href="{{ $currentCategory->link }}"
           class="btn btn--o-main">{!! returnWord('Cancel', WORDS_PROJECT) !!}</a>
    </div>
</form>
