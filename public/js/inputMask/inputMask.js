/**
 *
 * EXAMPLES
 *
 * $(selector).inputMask({...options})
 *
 * $('.js-price-input').inputMask({type: 'price'})
 * $('.js-card-input').inputMask({type: 'pattern', pattern: 'xxxx-xxxx-xxxx-xxxx'})
 * $('.js-eur-price-input').inputMask({type: 'price', mask: '{{value}} EUR'})
 *
 * */

(function($) {
  /**
   * type: price, float, number, letters, pattern
   *
   * inputLength
   *
   * decimals
   *
   * numberMin
   *
   * numberMax
   *
   * case: upper, lower
   *
   * pattern: 'x' - chars, '9' - numbers
   *
   * mask: {{value}} will be replaced by input value
   *
   */
  $.fn.inputMask = function(options) {
    const settings = $.extend({
      type: '',
      inputLength: 0,
      decimals: 0,
      case: '',
      pattern: '',
      mask: '',
      numberMin: null,
      numberMax: null,
    }, options);

    this.on('input', function() {
      let inputValue = $(this).val();
      const $input = $(this);

      // Получаем текущую позицию курсора
      const cursorPosition = $input[0].selectionStart;

      if (settings.inputLength > 0 && inputValue.length > settings.inputLength) {
        inputValue = inputValue.slice(0, settings.inputLength);
      }

      if (settings.case === 'upper') {
        inputValue = inputValue.toUpperCase();
      } else if (settings.case === 'lower') {
        inputValue = inputValue.toLowerCase();
      }

      switch (settings.type) {
        case 'price':
          inputValue = inputValue.replace(',', '.');
          if (!/^\d+(\.\d{0,2})?$/.test(inputValue)) {
            inputValue = inputValue.slice(0, -1);
          }
          break;
        case 'float':
          inputValue = inputValue.replace(',', '.');
          const regex = new RegExp(`^\\d+(\\.\\d{0,${settings.decimals}})?$`);
          if (!regex.test(inputValue)) {
            inputValue = inputValue.slice(0, -1);
          }
          break;
        case 'number':
          inputValue = inputValue.replace(/\D/g, '');

          if (settings.numberMin !== null || settings.numberMax !== null) {
            let numValue = parseInt(inputValue, 10);
            if (!isNaN(numValue)) {
              if (settings.numberMin !== null && numValue < settings.numberMin) {
                numValue = settings.numberMin;
              }
              if (settings.numberMax !== null && numValue > settings.numberMax) {
                numValue = settings.numberMax;
              }
              inputValue = numValue.toString();
            }
          }
          break;
        case 'letters':
          inputValue = inputValue.replace(/[^a-zA-ZА-яĀ-ž]/g, '');
          break;
        case 'pattern':
          if (settings.pattern) {
            let maskedValue = '';
            let maskIndex = 0;
            const maskChars = ['x', '9'];

            for (let i = 0; i < inputValue.length; i++) {
              if (maskIndex >= settings.pattern.length) break;

              const maskChar = settings.pattern[maskIndex];

              if (maskChars.includes(maskChar)) {
                if (maskChar === 'x' && /^[a-zA-ZА-яĀ-ž]+$/.test(inputValue[i])) {
                  maskedValue += inputValue[i];
                  maskIndex++;
                } else if (maskChar === '9' && /^\d+$/.test(inputValue[i])) {
                  maskedValue += inputValue[i];
                  maskIndex++;
                }
              } else {
                maskedValue += maskChar;
                if (inputValue[i] !== maskChar) i--;
                maskIndex++;
              }
            }
            inputValue = maskedValue;
          }
          break;
      }

      // Если есть шаблон (mask), оборачиваем inputValue
      if (settings.mask && inputValue.length > 0) {
        inputValue = settings.mask.replace('{{value}}', inputValue);
      }

      $(this).val(inputValue);

      if (settings.mask) {
        // Устанавливаем новое значение и фиксируем позицию курсора
        const maskValueStart = inputValue.indexOf('{{value}}');
        const maskValueEnd = inputValue.indexOf('{{value}}') + inputValue.length - 1;

        if (cursorPosition < maskValueStart || cursorPosition > maskValueEnd) {
          // если курсор не на введенном значении, то перемещаем его на конец введенного текста
          setTimeout(() => {
            $input[0].setSelectionRange(maskValueEnd, maskValueEnd);
          }, 0);
        }

      }
    });

    return this;
  };
})(jQuery);