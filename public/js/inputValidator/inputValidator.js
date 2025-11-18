(function($) {
  $.fn.inputValidator = function(options) {
    let settings = $.extend({
      required: false,
      email: false,
      minLength: 0,
      maxLength: 0,
      equalTo: '',
      associated: { // Изменено с 'with' на 'associated'
        selector: '',
        validateParams: {},
      },
      customValidation: null,
      withWrapper: false, // Дефолтное значение true
      onValidation: null,
    }, options);

    return this.each(function() {
      let $element = $(this);
      // Использование локальной переменной settings вместо this.settings
      let $wrapper = settings.withWrapper ? $element.closest('[data-input-wrapper]') : $element;

      // Вызываем валидацию при инициализации для основного элемента
      // validate(settings, $element);

      function validate(validateSettings, $currentElement) {

        // Валидация на основе настроек
        let value = getValue($currentElement);
        let isValid = true;

        if (validateSettings.required && value === '') {
          isValid = false;
        }

        if (validateSettings.email && !isValidEmail(value)) {
          isValid = false;
        }

        if (validateSettings.minLength > 0 && value.length < validateSettings.minLength) {
          isValid = false;
        }

        if (validateSettings.maxLength > 0 && value.length > validateSettings.maxLength) {
          isValid = false;
        }

        if (validateSettings.equalTo !== '') {
          let $otherElement = $(validateSettings.equalTo);
          if (value !== getValue($otherElement)) {
            isValid = false;
          }
        }

        if (validateSettings.customValidation && typeof validateSettings.customValidation === 'function') {
          isValid = validateSettings.customValidation(value, $currentElement) && isValid;
        }

        if (isValid && validateSettings.associated.selector !== '') {
          let $relatedElement = $(validateSettings.associated.selector);
          validate(validateSettings.associated.validateParams, $relatedElement);
        }

        // Обновление состояния элемента в зависимости от результата валидации
        updateElementState($wrapper, isValid);

        // Удаление js-input-error-msg, если инпут валиден
        if (isValid) {
          removeErrorMessages($currentElement);
        }

        // Вызов пользовательской функции после каждой валидации, если она определена
        if (typeof validateSettings.onValidation === 'function') {
          validateSettings.onValidation(isValid, $currentElement);
        }

        return isValid;
      }

      function updateElementState($elemWrapper, isValid) {
        let errorClass = 'input--error';
        if ($elemWrapper.is('textarea')) {
          errorClass = 'textarea--error';
        }
        if (isValid) {
          $elemWrapper.removeClass(errorClass).addClass('correct');
        } else {
          $elemWrapper.removeClass('correct').addClass(errorClass);
        }
      }

      function getValue($el) {
        let type = $el.prop('type');
        if (type === 'checkbox' || type === 'radio') {
          return $el.prop('checked');
        } else if ($el.is('select')) {
          return $el.val() || '';
        } else {
          return ($el.val() || '').trim();
        }
      }

      function isValidEmail(email) {
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
      }

      function removeErrorMessages($currentElement) {
        // Удаление всех сообщений, которые относятся к текущему элементу
        let field = $currentElement.attr('name');
        $(`[data-input-error-msg="${field}"]`).remove();
      }

      $element.on('input change', function() {
        validate(settings, $element);
      });
    });
  };
})(jQuery);
