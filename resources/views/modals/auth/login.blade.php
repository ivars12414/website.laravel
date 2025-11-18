<div class="remodal__body">
    <div class="block__title">{!! returnWord('Sign In', WORDS_INTERFACE) !!}</div>

    <p class="text">{!! returnWord('Enter your credentials to continue.', WORDS_INTERFACE) !!}</p>

    <form class="form js-login-form" method="post" action="#" onsubmit="return false;" data-form="login">
        @csrf
        <div class="form__group">
            <label class="label" for="modal-login-email">{!! returnWord('Email', WORDS_INTERFACE) !!}</label>
            <input type="email" id="modal-login-email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form__group">
            <label class="label" for="modal-login-password">{!! returnWord('Password', WORDS_INTERFACE) !!}</label>
            <input type="password" id="modal-login-password" name="password" required>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--main" disabled>
                {!! returnWord('Sign In', WORDS_INTERFACE) !!}
            </button>
        </div>
    </form>
</div>
