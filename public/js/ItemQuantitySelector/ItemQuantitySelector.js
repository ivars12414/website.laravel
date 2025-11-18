class ItemQuantitySelector {
  constructor({
                itemFormSelector,
                onIncrease,
                onDecrease,
                onChange,
                minQty = 1,
                maxQty = 999,
              }) {
    this.itemFormSelector = itemFormSelector;

    this.globalMinQty = minQty;
    this.globalMaxQty = maxQty;

    this.onIncrease = onIncrease || (() => {
    });
    this.onDecrease = onDecrease || (() => {
    });
    this.onChange = onChange || (() => {
    });

    this.bindEvents();
  }

  // получаем min/max для конкретной формы (учитываем data-min / data-max)
  getLimits($form) {
    const formMin = parseInt($form.attr('data-min'), 10);
    const formMax = parseInt($form.attr('data-max'), 10);

    return {
      minQty: !isNaN(formMin) ? formMin : this.globalMinQty,
      maxQty: !isNaN(formMax) ? formMax : this.globalMaxQty,
    };
  }

  bindEvents() {
    const self = this;

    // Клик по + или -
    $(document).on('click', `${this.itemFormSelector} [data-action]`, function(e) {
      e.preventDefault();

      const $btn = $(this);
      const action = $btn.attr('data-action');

      const $form = $btn.closest(self.itemFormSelector);
      const $input = $form.find('[data-input]');

      if (!$input.length) return;

      const { minQty, maxQty } = self.getLimits($form);

      let qty = parseInt($input.val(), 10);
      if (isNaN(qty)) qty = minQty;

      if (action === '+') {
        if (qty < maxQty) qty += 1;
        self.onIncrease({ itemForm: $form[0], qty });
      } else if (action === '-') {
        if (qty > minQty) qty -= 1;
        self.onDecrease({ itemForm: $form[0], qty });
      }

      self.updateInput($input, $form, qty);
    });

    // Ограничение ввода в инпуте (только цифры, backspace, arrows и т.д.)
    $(document).on('keydown', `${this.itemFormSelector} [data-input]`, function(e) {
      // Разрешаем: ctrl/cmd/alt комбинации, backspace, tab, enter, стрелки, delete
      const allowedKeyCodes = [8, 9, 13, 37, 39, 46];
      const isControlCombo = e.ctrlKey || e.metaKey || e.altKey;

      if (
        !isControlCombo &&
        !allowedKeyCodes.includes(e.keyCode) &&
        (e.key < '0' || e.key > '9')
      ) {
        e.preventDefault();
      }
    });

    // Ввод вручную — clamp по min/max
    $(document).on('input', `${this.itemFormSelector} [data-input]`, function() {
      const $input = $(this);
      const $form = $input.closest(self.itemFormSelector);

      const { minQty, maxQty } = self.getLimits($form);

      let qty = parseInt($input.val(), 10);

      if (isNaN(qty)) {
        qty = minQty;
      } else {
        qty = Math.max(minQty, Math.min(maxQty, qty));
      }

      self.updateInput($input, $form, qty);
    });
  }

  updateInput($input, $form, qty) {
    $input.val(qty);
    this.onChange({ itemForm: $form[0], qty });
  }
}
