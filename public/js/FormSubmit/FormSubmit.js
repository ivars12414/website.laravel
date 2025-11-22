class FormSubmit {
    constructor(options) {
        this.ajaxUrl = options.ajaxUrl;
        this.formSelector = options.formSelector;
        this.type = options.type ?? 'POST';
        this.dataType = options.dataType ?? 'JSON';
        this.alertClass = options.alertClass ?? 'alert';
        this.successClass = options.successClass ?? 'alert--green';
        this.errorClass = options.errorClass ?? 'alert--red';
        this.beforeSendCallback = options.beforeSendCallback;
        this.afterSendCallback = options.afterSendCallback;
        this.successCallback = options.successCallback;
        this.errorCallback = options.errorCallback;
        this.defaultSuccessResponseActions = options.defaultSuccessResponseActions ?? true;
        this.defaultErrorResponseActions = options.defaultErrorResponseActions ?? true;
        this.resetFormOnSuccess = options.resetFormOnSuccess ?? false;
        this.hideFormOnSuccess = options.hideFormOnSuccess ?? false;
        this.showAlertOnSuccess = options.showAlertOnSuccess ?? true;
        this.showAlertOnError = options.showAlertOnError ?? true;
        this.multipart = options.multipart ?? false;
        this.toastAlerts = options.toastAlerts ?? false;
        this.createDefaultAlerts = options.createDefaultAlerts ?? !this.toastAlerts;
        this.alertTimeout = options.alertTimeout;  // milliseconds
        this.alertPosition = options.alertPosition ?? 'before';  // default position
        this.fadeInTime = options.fadeInTime ?? 500; // Fade in time in milliseconds
        this.fadeOutTime = options.fadeOutTime ?? 500; // Fade out time in milliseconds
        this.alertsCreated = !this.createDefaultAlerts;

        this.scrollToAlert = options.scrollToAlert ?? false;
        this.scrollToAlertSpeed = options.scrollToAlertSpeed ?? 700;
        this.scrollToAlertOffset = options.scrollToAlertOffset ?? 100;

        this.validationRules = options.validationRules ?? {};

        this.submitOnChange = options.submitOnChange ?? false;
        this.writeQueryParams = options.writeQueryParams ?? false;
        this.addData = options.addData ?? {};

        this.encodeBase64Fields = options.encodeBase64Fields ?? [];

        this.init();
    }

    init() {
        this.clickedButtonValue = null;

        if (this.createDefaultAlerts) {
            this.createAlertBlocks();
        }
        this.setupValidation();

        document.addEventListener('click', (e) => {
            if (
                e.target.matches(`${this.formSelector} button[type="submit"]`) ||
                e.target.closest(`${this.formSelector} button[type="submit"]`)
            ) {
                const button = e.target.closest('button[type="submit"]');
                const name = button.getAttribute('name');
                const value = button.getAttribute('value');

                if (name) {
                    this.clickedButtonValue = {name, value};
                } else {
                    this.clickedButtonValue = null;
                }
            }
        });

        $(document).on(this.submitOnChange ? 'change' : 'submit', this.formSelector, (e) => this.handleSubmit(e));
    }

    createAlertBlocks() {

        const $form = $(this.formSelector);
        const alertHtml = `<div class='js-alert ${this.alertClass}' data-form='${$form.data('form')}' data-group="default" style='display: none;'></div>`;

        if ($form.length > 0) {
            this.alertsCreated = true;
        }

        switch (this.alertPosition) {
            case 'before':
                $form.before(alertHtml);
                break;
            case 'after':
                $form.after(alertHtml);
                break;
            case 'prepend':
                $form.prepend(alertHtml);
                break;
            case 'append':
                $form.append(alertHtml);
                break;
            default:
                $(this.alertPosition).append(alertHtml);
                break;
        }

    }

    handleSubmit(event) {
        event.preventDefault();

        this.$form = $(event.currentTarget);

        if (this.createDefaultAlerts && !this.alertsCreated) {
            this.createAlertBlocks();
        }

        const $form = this.$form;
        const formData = this.encodeFields(this.multipart ? new FormData($form[0]) : $form.serializeArray());

        if (this.clickedButtonValue) {
            if (this.multipart) {
                formData.append(this.clickedButtonValue.name, this.clickedButtonValue.value);
            } else {
                formData.push(this.clickedButtonValue);
            }
        }

        if (this.addData) {
            if (this.multipart) {
                $.each(this.addData, function (data, value) {
                    formData.append(data, value);
                });
            } else {
                formData.push(this.addData);
            }
        }
        const $submit = $form.find(':submit');
        const $spinner = $form.find('.js-form-spinner');

        if (this.writeQueryParams) {
            let tmpFormData = this.multipart ? formData : new FormData($form[0]);

            // исключения
            // $('[data-search-input]').each(function() {
            //   formData.delete($(this).attr('name'));
            // });

            const queryString = new URLSearchParams(tmpFormData).toString();

            window.history.pushState({}, document.getElementsByTagName('title')[0].innerHTML, '?' + queryString);
        }

        // Очистка и подготовка UI компонентов
        $spinner.show();
        $submit.prop('disabled', true);

        if (this.defaultSuccessResponseActions || this.defaultErrorResponseActions) {
            $(`.js-alert[data-form=${$form.data('form')}]`).hide().html('');
            $form.find('input, select, textarea, .input__wrapper').removeClass('error').removeClass('success').removeClass('textarea--error');
            $form.find('.js-input-error-msg').remove();
        }
        if (this.defaultSuccessResponseActions) {
            $(`.js-alert[data-form=${$form.data('form')}]`).removeClass(`${this.successClass}`);
        }
        if (this.defaultErrorResponseActions) {
            $(`.js-alert[data-form=${$form.data('form')}]`).removeClass(`${this.errorClass}`);
        }

        if (this.beforeSendCallback) {
            this.beforeSendCallback(this, {$form, $spinner, $submit, formData});
        }

        // Выполнение AJAX запроса
        $.ajax({
            type: this.type,
            url: this.ajaxUrl,
            data: formData,
            dataType: this.dataType,
            contentType: this.multipart ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
            processData: !this.multipart,
            headers: {
                'Accept-Language': document.documentElement.lang || 'en',
            },
            success: (response) => this.handleResponse(response, $form, $submit, $spinner),
            error: (xhr, status, error) => {

                // Обработка ошибок AJAX запроса с попыткой вывести ответ сервера
                const response = this.extractErrorResponse(xhr);

                if (response) {
                    // Прогоняем ответ через стандартный обработчик, чтобы отрисовать ошибки
                    this.handleResponse(response, $form, $submit, $spinner);
                } else {
                    this.showAlert('default', 'Произошла ошибка при отправке формы.', this.errorClass);
                    $spinner.hide();
                    $submit.prop('disabled', false);
                }
            },
            complete: () => {
                if (this.afterSendCallback) {
                    this.afterSendCallback(this, {$form, $submit, $spinner});
                }
            },
        });
    }

    handleResponse(response, $form, $submit, $spinner) {
        $spinner.hide();
        $submit.prop('disabled', false);

        if (!response.error) {
            if (this.resetFormOnSuccess) {
                $form.trigger('reset');
            }
            if (this.defaultSuccessResponseActions) {
                if (this.showAlertOnSuccess) {
                    this.showAlert('default', response.msg, this.successClass);
                }
                if (this.toastAlerts) {
                    if (response.msg) {
                        toastr.success(response.msg);
                    }
                }
            }
            if (this.hideFormOnSuccess) {
                $form.hide();
            }
            if (this.successCallback) {
                this.successCallback(response, {$form, $submit, $spinner});
            }
        } else {
            if (this.defaultErrorResponseActions) {
                if (this.toastAlerts) {
                    if (this.toastAlerts) {
                        if (response['msg']) {
                            toastr.error(response['msg']);
                        }
                    }
                }
                $.each(response.error_fields, (field, data) => {
                    let $input = $form.find(`[name="${field}"]`);
                    if ($input.data('wrapper')) {
                        $input.closest($input.data('wrapper')).addClass('error');
                    } else {
                        if ($input.is('textarea')) {
                            $input.addClass('textarea--error');
                        } else {
                            $input.addClass('error');
                        }
                    }
                    if (data.msg) {
                        if ($input.attr('type') === 'checkbox') {
                            $input.closest('.checkbox').after(`<span class='js-input-error-msg' data-input-error-msg="${field}">${data.msg}</span>`);
                        } else {
                            if ($input.data('wrapper')) {
                                $input.closest($input.data('wrapper')).after(`<span class='js-input-error-msg' data-input-error-msg="${field}">${data.msg}</span>`);
                            } else {
                                $input.after(`<span class='js-input-error-msg' data-input-error-msg="${field}">${data.msg}</span>`);
                            }
                        }
                    }
                });
                $.each(response.empty_groups, (group, msg) => {
                    if (this.showAlertOnError) {
                        this.showAlert(group, msg, this.errorClass);
                    }
                    if (this.toastAlerts) {
                        if (msg) {
                            toastr.error(msg);
                        }
                    }
                });
            }
            if (this.errorCallback) {
                this.errorCallback(response, {$form, $submit, $spinner});
            }
        }
    }

    extractErrorResponse(xhr) {
        let response = xhr.responseJSON ?? null;

        if (!response && xhr.responseText) {
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                response = null;
            }
        }

        if (response && typeof response.error === 'undefined') {
            response.error = true;
        }

        return response;
    }

    setAutoHideAlert($alert) {
        if (this.alertTimeout) {
            setTimeout(() => $alert.fadeOut(this.fadeOutTime), this.alertTimeout);
        }
    }

    showAlert(group, content, alertClass) {
        const $form = $(this.formSelector);
        const $alert = $(`.js-alert[data-form=${$form.data('form')}][data-group=${group}]`);

        // Добавляем текстовое содержимое и класс к блоку алерта
        $alert.html(content).addClass(alertClass).fadeIn(this.fadeInTime);

        if (this.scrollToAlert) {
            this.scrollToElement($alert);
        }

        // Устанавливаем автоматическое скрытие алерта, если задано время таймаута
        this.setAutoHideAlert($alert);
    }

    scrollToElement(selector) {
        var element;

        if (selector) {
            element = $(selector);
        }

        if (element && element.length) { // Проверка, существует ли элемент
            $('html, body').animate({
                scrollTop: element.offset().top - this.scrollToAlertOffset,
            }, this.scrollToAlertSpeed); // Анимация прокрутки с длительностью 1000 миллисекунд (1 секунда)
        } else {
            $('html, body').animate({
                scrollTop: 0,
            }, this.scrollToAlertSpeed); // Прокрутка до верха страницы с длительностью 1000 миллисекунд (1 секунда)
        }
    }

    setupValidation() {
        const $form = $(this.formSelector);
        for (const [fieldName, rules] of Object.entries(this.validationRules)) {
            const $field = $form.find(`[name="${fieldName}"]`);
            if ($field.length) {
                $field.inputValidator(rules);
            }
        }
    }

    encodeFields(formData) {
        if (!this.encodeBase64Fields.length) return formData;

        let encodedFields = []; // Список реально закодированных полей

        const shouldEncode = (fieldName) => {
            return this.encodeBase64Fields.some((pattern) => {
                if (pattern === fieldName) return true;
                if (pattern.includes('[*]')) {
                    const baseName = pattern.replace('[*]', '');
                    return fieldName.startsWith(baseName + '[');
                }
                return false;
            });
        };

        if (this.multipart) {
            let newFormData = new FormData();
            formData.forEach((value, key) => {
                if (shouldEncode(key)) {
                    if (!(value instanceof File)) {
                        value = btoa(unescape(encodeURIComponent(value))); // Кодируем в Base64
                        encodedFields.push(key); // Запоминаем закодированное поле
                    }
                }
                newFormData.append(key, value);
            });
            newFormData.append('_encoded_fields', JSON.stringify(encodedFields)); // Передаём список закодированных полей
            return newFormData;
        } else {
            formData.forEach((item) => {
                if (shouldEncode(item.name)) {
                    item.value = btoa(unescape(encodeURIComponent(item.value)));
                    encodedFields.push(item.name);
                }
            });
            formData.push({name: '_encoded_fields', value: JSON.stringify(encodedFields)}); // Добавляем в обычный formData
            return formData;
        }
    }

}
