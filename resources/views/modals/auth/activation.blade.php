<a href="#" class="remodal-close" data-modal-close></a>

<div class="remodal__body">
    <form class="form js-activation-code-form" data-form="activation-code" method="post" action="#" onsubmit="return false;">
        @csrf
        <input type="hidden" name="client_mail" id="client_mail"
               value="{{ e($mail) }}">
        <div class="block__title js-title"><strong><?php getWord('Activation', WORDS_PROJECT); ?></strong></div>

        <div class="js-alert alert" data-form="activation-code" data-group="default" style="display: none"></div>

        <div class="form__block">
            <label for="activation-code" class="label"><?php getword("Activation code", WORDS_INTERFACE); ?>:</label>
            <input type="text" name="act_code" id="activation-code" class="input"
                   autocomplete="off">
        </div>

        <div class="form__block">
            <div id="countdown-wrapper">
                <?php getWord('New code available after', WORDS_PROJECT) ?>&nbsp;<span
                        id="countdown">{{ $seconds }}</span>&nbsp;<?php getword("s.", WORDS_INTERFACE); ?>
            </div>
        </div>

        <div class="form__block js-resend-wrapper" style="display: none;">
            <?= returnWord('Have not received activation code?', WORDS_INTERFACE) ?>
            <a href="#" class="js-resend-button"><?= returnWord('Resend', WORDS_INTERFACE) ?></a>
        </div>

        <div class="form__block">
            <?php submitButton(['text' => returnWord('Submit', WORDS_INTERFACE)]); ?>
        </div>
    </form>
</div>
