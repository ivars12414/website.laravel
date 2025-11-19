<form class="cart__footer" data-form="apply_promo">
    <div class="form__row">
        <div class="form__block">
            <label for="sign-in-email" class="label">{!! returnWord("Promo code", WORDS_INTERFACE) !!}:</label>
            <input type="text" name="code" @class([
            'input',
            'success' => $summary['promo_code']
        ]) class="input" autocomplete="off" value="{{ $summary['promo_code']->code ?? '' }}">
            @if($summary['promo_code'])
                <span class='js-input-error-msg' data-input-error-msg="code">{!! returnWord('Promo %PROMO_NAME% applied successfully', WORDS_PROJECT, [
                            '%PROMO_NAME%' => $summary['promo_code']->name ?? '',
                            '%PROMO_CODE%' => $summary['promo_code']->code ?? '',
                    ]) !!}</span>
            @endif
        </div>

        <div class="form__block">
            <button class="btn btn--main" type="submit">
            <span
                class="js-form-spinner loading-circle loading-circle--btn loading-circle--white"
                style="display: none;"></span>
                {!! returnWord('Apply', WORDS_INTERFACE) !!}
            </button>
            <a href="#" data-remove-promo @if(!$summary['promo_code']) style="display: none;" @endif>
                <img src="/images/delete.svg" alt="" aria-hidden="true">
            </a>
        </div>
    </div>
</form>
