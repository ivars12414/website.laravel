class ModalManager {
  static #modalStack = [];
  modalContentsCache = {};
  _suppressStackReset = false;
  _currentModalClass = null;
  callerCallbacks = new Map();

  constructor(options = {}) {
    this.defaultOptions = {
      callerSelector: '[data-modal-caller]',
      closeSelector: '[data-modal-close]',
      htmlResponse: false,
      closeOnEscape: true,
      closeOnOutsideClick: false,
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      zIndexBase: 1000,
      actionCatalog: '',
      urlParams: {},
      ...options,
    };

    this._initModalWrapper();
    this.bindCallerEvents();
    this.bindGlobalEvents();
  }

  _initModalWrapper() {
    const $modalBlock = $(`
        <div class="remodal">
          <a href="#" data-modal-close class="close"></a>
          <div data-modal-spinner style="display: none; text-align: center;margin: 5rem;">
            <span class="loading-circle"></span>
          </div>
          <div class="remodal__content" data-modal-fetcher></div>
        </div>
    `);

    $('body').append($modalBlock);

    this.$modalBlock = $modalBlock;
    this.$modal = $modalBlock.remodal(this.defaultOptions);
    this.$modalFetcher = $modalBlock.find('[data-modal-fetcher]');
    this.$modalSpinner = $modalBlock.find('[data-modal-spinner]');

    this.$modalBlock.on('click', this.defaultOptions.closeSelector, (e) => {
      e.preventDefault();
      this._popModalFromStack();
    });

    if (this.defaultOptions.closeOnOutsideClick) {
      $(document).on('mousedown', (e) => {
        const $target = $(e.target);
        const clickedOutside = !$target.closest('.remodal').length;
        if (clickedOutside) this._popModalFromStack();
      });
    }
  }

  static getCurrentContent() {
    return this.#modalStack[this.#modalStack.length - 1];
  }

  bindCallerEvents() {
    $(document).on('click', this.defaultOptions.callerSelector, (e) => {
      e.preventDefault();

      const modalCaller = $(e.target).closest(this.defaultOptions.callerSelector);

      if (!modalCaller.length) return;

      const data = modalCaller.data();

      const selectorKey = `selector:${modalCaller.selector || modalCaller.attr('id') || ''}`;
      const actionKey = `action:${data.action || ''}`;

      const callback =
        this.callerCallbacks.get(selectorKey) ||
        this.callerCallbacks.get(actionKey);

      if (typeof callback === 'function') callback({ modalCaller, data });

      this.show(data);

    });
  }

  bindGlobalEvents() {
    if (this.defaultOptions.closeOnEscape) {
      $(document).on('keydown', (e) => {
        if (e.key === 'Escape') this._popModalFromStack();
      });
    }
  }

  _pushModalToStack(content) {
    ModalManager.#modalStack.push(content);
    this._renderCurrentContent();
    if (ModalManager.#modalStack.length === 1) {
      this.$modal.open();
    }
  }

  _popModalFromStack() {
    if (ModalManager.#modalStack.length === 0) return;
    ModalManager.#modalStack.pop();

    if (ModalManager.#modalStack.length === 0) {
      this.$modal.close();
      setTimeout(() => {
        this.$modalFetcher.empty();
        if (this._currentModalClass) {
          this.$modalBlock.removeClass(this._currentModalClass);
          this._currentModalClass = null;
        }
      }, 300);
    } else {
      this._renderCurrentContent();
    }
  }

  _clearStack({ preserveOpen = false } = {}) {
    ModalManager.#modalStack = [];

    // Prevent calling close if already opened and preserveOpen flag is set
    if (!preserveOpen && (!this.$modal.getState || this.$modal.getState() !== 'opened')) {
      this.$modal.close();
    }

    this.$modalFetcher.empty();

    if (this._currentModalClass) {
      this.$modalBlock.removeClass(this._currentModalClass);
      this._currentModalClass = null;
    }

  }

  _renderCurrentContent() {
    const current = ModalManager.getCurrentContent();
    if (!current) return;
    this.$modalSpinner.hide();
    this.$modalFetcher.html(current.html);
    if (typeof current.onShow === 'function') current.onShow();
  }

  show(data = {}, html = null) {
    const shouldKeepStack = data && data.stack !== undefined && data.stack !== false && data.stack !== 'false';

    if (!shouldKeepStack && !this._suppressStackReset) {
      this._clearStack({ preserveOpen: true });
    }

    // handle modal class
    if (this._currentModalClass) {
      this.$modalBlock.removeClass(this._currentModalClass);
      this._currentModalClass = null;
    }
    if (data && data.modalClass) {
      this._currentModalClass = data.modalClass;
      this.$modalBlock.addClass(this._currentModalClass);
    }

    if (html) {
      this._pushModalToStack({ html });
    } else {
      this.ajaxFetch(data);
    }
  }

  ajaxFetch(data) {
    if (!data.action) {
      console.warn('No action specified for AJAX fetch');
      return;
    }

    const useCache = data.cache !== undefined && data.cache !== false;
    const cacheKey = this.generateCacheKey(data);
    if (useCache && this.modalContentsCache[cacheKey]) {
      this._pushModalToStack({ html: this.modalContentsCache[cacheKey] });
      return;
    }

    this.$modalSpinner.show();

    $.ajax({
      url: this.buildUrl(`${this.defaultOptions.actionCatalog}/${data.action}.php`, this.defaultOptions.urlParams),
      type: 'POST',
      dataType: this.defaultOptions.htmlResponse ? 'HTML' : 'JSON',
      data,
      success: (response) => {
        const html = this.defaultOptions.htmlResponse ? response : response.html;

        if (!this.defaultOptions.htmlResponse && response.modal_class) {
          if (this._currentModalClass) {
            this.$modalBlock.removeClass(this._currentModalClass);
            this._currentModalClass = null;
          }
          this.$modalBlock.addClass(response.modal_class);
          this._currentModalClass = response.modal_class;
        }

        this._pushModalToStack({ html });
        if (useCache) this.modalContentsCache[cacheKey] = html;
      },
      error: () => this._popModalFromStack(),
      complete: () => this.$modalSpinner.hide(),
    });
  }

  confirm(message, onConfirm, onCancel, template) {
    let html = typeof template === 'function' ? template() : `
      <div class="remodal__body">
        <div class="block__title" style="word-wrap: break-word;">
          ${message}
        </div>
    
        <div class="form__actions">
          <button class="btn btn--main js-modal-confirm">${this.defaultOptions.confirmButtonText}</button>
          <button class="btn btn--o-main js-modal-cancel">${this.defaultOptions.cancelButtonText}</button>
        </div>
      </div>
    `;
    html += `
      <script>
        $(function() {
          $('.js-modal-confirm').trigger('focus');
        });
      </script>
    `;

    this._suppressStackReset = true;
    this.show({}, html);
    this._suppressStackReset = false;

    this.$modalBlock.off('click', '.js-modal-confirm');
    this.$modalBlock.off('click', '.js-modal-cancel');

    this.$modalBlock.on('click', '.js-modal-confirm', (e) => {
      e.preventDefault();
      if (onConfirm) onConfirm();
      this._popModalFromStack();
    });

    this.$modalBlock.on('click', '.js-modal-cancel', (e) => {
      e.preventDefault();
      if (onCancel) onCancel();
      this._popModalFromStack();
    });
  }

  buildUrl(baseUrl, params) {
    const url = new URL(baseUrl, window.location.origin);
    Object.entries(params || {}).forEach(([key, value]) => url.searchParams.append(key, value));
    return url.href;
  }

  generateCacheKey(data) {
    const dataCopy = { ...data };
    delete dataCopy.cache;
    const sortedString = JSON.stringify(
      Object.keys(dataCopy).sort().reduce((obj, key) => ({ ...obj, [key]: dataCopy[key] }), {}),
    );
    let hash = 0;
    for (let i = 0; i < sortedString.length; i++) {
      hash = (hash << 5) - hash + sortedString.charCodeAt(i);
      hash |= 0;
    }
    return hash.toString();
  }

  setOptions(options) {
    this.defaultOptions = { ...this.defaultOptions, ...options };
  }

  close() {
    this._popModalFromStack();
  }

  bindConfirmCaller({ callerSelector, onConfirm, onCancel, template, titleCallback }) {

    if (!callerSelector) return;

    $('body').on('click', callerSelector, (e) => {
      e.preventDefault();
      const $button = $(e.currentTarget);
      // если template — функция, замыкаем data
      const preparedTemplate = typeof template === 'function'
        ? () => template($button.data())
        : template;

      const preparedOnConfirm = typeof onConfirm === 'function'
        ? () => onConfirm($button.data(), $button)
        : onConfirm;

      const preparedOnCancel = typeof onCancel === 'function'
        ? () => onCancel($button.data(), $button)
        : onCancel;

      const title = typeof titleCallback === 'function'
        ? titleCallback($button.data(), $button)
        : '';

      this.confirm(title, preparedOnConfirm, preparedOnCancel, preparedTemplate);
    });
  }

}