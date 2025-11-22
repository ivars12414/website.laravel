<a href="#" class="remodal-close" data-modal-close></a>

<div class="remodal__body">
    <form class="form" data-form="top_up">

        @csrf

        <div class="block__title js-title">{!! returnWord('Top up modal title', WORDS_PROJECT) !!}</div>

        <div class="form__block">
            <label for="top_up-value" class="label">{!! returnWord('Amount', WORDS_PROJECT) !!}:</label>
            <input type="text" class="input" id="top_up-value" name="amount">
        </div>

        @if (isConfig('agree_in_top_up'))
            <div class="form__block">
                <div class="checkbox">
                    <input type="checkbox" id="top_up__checkbox" class="checkbox__input" name="agree">
                    <label for="top_up__checkbox"
                           class="checkbox__label">{!! returnWord('agree text', WORDS_PROJECT) !!}</label>
                </div>
            </div>
        @endif

        {!! $payment_methods_block !!}

        <div class="form__block">
            <button type="submit" class="btn btn--main">
                {!! returnWord('Top up', WORDS_INTERFACE) !!}
            </button>
        </div>
    </form>
</div>
