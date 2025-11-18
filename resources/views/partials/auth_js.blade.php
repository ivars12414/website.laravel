<script>

    function showClientTypeRegFields() {

        let client_type = $(`[data-client-type-radio]:checked`).val()

        $(`[data-client-type-reg-fields-wrapper]`).hide()
        $(`[data-client-type-reg-fields-wrapper=${client_type}]`).show()

    }

    var countdown = <?= ACTIVATION_CODE_RESEND_COUNTDOWN ?? 0 ?>;
    var timer;

    function startCountdown() {
        clearInterval(timer);
        timer = setInterval(function () {
            countdown--;
            $('#countdown').text(countdown);

            if (countdown <= 0) {
                clearInterval(timer);
                $('#countdown-wrapper').hide();
                $('.js-resend-wrapper').show();
            }
        }, 1000);
    }

    function resetCountdown() {
        countdown = <?= ACTIVATION_CODE_RESEND_COUNTDOWN ?? 0 ?>;
        clearInterval(timer);
    }

    $(function () {

        $(document).on('closed', MODAL.defaultOptions.modalSelector, () => {
            resetCountdown();
        });

        new FormSubmit({
            formSelector: '.js-login-form',
            ajaxUrl: "{{ route('auth.login') }}",
            resetFormOnSuccess: true,
            showAlertOnSuccess: false,
            createDefaultAlerts: false,
            successCallback: (response) => {
                if (response.redirect_href) {
                    location.href = response.redirect_href;
                } else {
                    location.href = '{{ $_SESSION['last_address'] ?? request()->fullUrl() }}';
                }
            },
            errorCallback: (response) => {
                if (response.activation_form) {
                    MODAL.show({
                        action: 'auth/activation',
                        mail: response.mail
                    });
                    startCountdown();
                }
            }
        });

        new FormSubmit({
            formSelector: '.js-registration-form',
            ajaxUrl: "/modules/auth/registration/handler.php?lid=<?= lang()->id ?>",
            resetFormOnSuccess: true,
            showAlertOnSuccess: false,
            createDefaultAlerts: false,
            successCallback: (response) => {
                if (response.redirect_href) {
                    location.href = response.redirect_href;
                }
                if (response.activation_form) {
                    MODAL.show({
                        action: 'auth/activation',
                        mail: response.mail
                    });
                    startCountdown();
                }
            },
        });

        new FormSubmit({
            formSelector: '.js-reminder-form',
            ajaxUrl: "/modules/auth/reminder/handler.php?lid=<?= lang()->id ?>",
            resetFormOnSuccess: true,
            createDefaultAlerts: false,
            successCallback: (response) => {
                if (response.activation_form) {
                    MODAL.show({
                        action: 'auth/reminder_activation',
                        mail: response.mail
                    });
                    startCountdown();
                }
            },
        });

        new FormSubmit({
            formSelector: '.js-activation-code-form',
            ajaxUrl: "/modules/auth/activation/handler.php?lid=<?= lang()->id ?>",
            resetFormOnSuccess: true,
            createDefaultAlerts: false,
            showAlertOnSuccess: false,
            successCallback: (response) => {
                if (response.redirect_href) {
                    location.href = response.redirect_href;
                } else {
                    location.reload();
                }
            }
        });

        new FormSubmit({
            formSelector: '.js-restore-enter-code-form',
            ajaxUrl: "/modules/auth/reminder_activation/handler.php?lid=<?= lang()->id ?>",
            resetFormOnSuccess: true,
            showAlertOnSuccess: false,
            createDefaultAlerts: false,
            successCallback: (response) => {
                if (response.redirect_href) {
                    location.href = response.redirect_href;
                }
                if (response.activation_form) {
                    MODAL.show({
                        action: 'auth/change_pass',
                        mail: response.mail,
                        recovery_code: response.recovery_code
                    });
                }
            }
        });

        new FormSubmit({
            formSelector: '.js-change-password',
            ajaxUrl: "/modules/auth/change_pass/handler.php?lid=<?= lang()->id ?>",
            resetFormOnSuccess: true,
            showAlertOnSuccess: false,
            createDefaultAlerts: false,
            successCallback: (response) => {
                if (response.redirect_href) {
                    location.href = response.redirect_href;
                } else {
                    location.reload();
                }
            }
        });

        $('body').on('click', '.js-resend-button', function (e) {
            e.preventDefault();

            var success = 'alert--green',
                error = 'alert--red';

            const $form = $(this).closest('form');

            let $alerts = $(`.js-alert[data-form=${$form.data('form')}]`);

            $alerts.hide().removeClass(success).removeClass(error).html("");
            $.ajax({
                url: "/modules/auth/ajax/resend_activation_code.php?lid=<?= lang()->id ?>",
                type: 'POST',
                dataType: 'JSON',
                data: $form.serialize(),
                success: function (data) {
                    if (!data['error']) {
                        countdown = <?= ACTIVATION_CODE_RESEND_COUNTDOWN ?? 0 ?>;
                        $('#countdown').text(countdown);
                        $('.js-resend-wrapper').hide();
                        $('#countdown-wrapper').show();
                        startCountdown();

                        $(".js-alert[data-form=" + $form.data('form') + "][data-group='default']").html(data['msg']).addClass(success).show();
                    } else {
                        $.each(data['error_fields'], function (field, msg) {
                            if (msg['msg'] === "") {
                                $this.find("[name=" + field + "]").addClass(msg['class']);
                            } else {
                                $this.find("[name=" + field + "]").addClass(msg['class']).after(msg['msg']);
                            }
                        });
                        $.each(data['empty_groups'], function (group, msg) {
                            $(".js-alert[data-form=" + $form.data('form') + "][data-group=" + group + "]").html(msg).addClass(error).show();
                        });
                    }
                }
            });
        });

    })

</script>
