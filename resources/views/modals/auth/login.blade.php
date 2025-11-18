<a href="#" class="remodal-close" data-modal-close></a>
<div class="remodal__body">
    <div class="block__title">{!! returnWord('Sign In', WORDS_INTERFACE) !!}</div>

    <form class="form js-login-form" method="post" action="#" onsubmit="return false;" data-form="login">
        @csrf

        <?php if (!empty($_POST['text'])) { ?>
        <div class="block__text"><?= $_POST['text'] ?></div>
        <?php } ?>

        <div class="form__block">
            <label for="modal-sign-in-email" class="label">{!!  returnWord("E-mail", WORDS_INTERFACE) !!}:</label>
            <input type="email" name="mail" id="modal-sign-in-email" class="input"
                   autocomplete="email">
        </div>

        <div class="form__block">
            <label class="label" for="modal-login-password">{!! returnWord('Password', WORDS_INTERFACE) !!}</label>
            <input type="password" id="modal-login-password" class="input" name="password">
        </div>

        <div class="form__row">
            <div class="form__block">
                <button type="submit" class="btn btn--main" disabled>
                    {!! returnWord('Sign In', WORDS_INTERFACE) !!}
                </button>
            </div>

            <a href="#" class="block__link" data-modal-caller data-action="auth/reminder"
               data-cache>{!! returnWord("Forgot password", WORDS_INTERFACE) !!}</a>

        </div>
    </form>
</div>

<div class="remodal__footer">
    <div class="block__text">
        {!! returnWord("Dont have an account?", WORDS_INTERFACE) !!}
        <a href="#" data-modal-caller data-action="auth/registration"
           data-cache>{!! returnWord("Sign Up!", WORDS_INTERFACE) !!}</a></div>
</div>
