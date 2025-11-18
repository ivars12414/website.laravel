class CopyToClipboard {
  constructor(options) {
    this.copySelector = options.copySelector;
    this.targetSelector = options.targetSelector;
    this.beforeCopyCallback = options.beforeCopyCallback;
    this.afterCopyCallback = options.afterCopyCallback;
    this.successCallback = options.successCallback;
    this.errorCallback = options.errorCallback;

    this.init();
  }

  init() {
    $(document).on('click', this.copySelector, (e) => this.handleCopy(e));
  }

  handleCopy(event) {
    event.preventDefault();

    const $target = $(event.currentTarget);
    let textToCopy = $target.data('copy-text');
    if (this.targetSelector) {
      textToCopy = $(this.targetSelector).html();
    }

    if (this.beforeCopyCallback) {
      this.beforeCopyCallback($target);
    }

    const success = this.copyTextToClipboard(textToCopy);

    if (success) {
      if (this.successCallback) {
        this.successCallback($target, textToCopy);
      }
    } else {
      if (this.errorCallback) {
        this.errorCallback($target);
      }
    }

    if (this.afterCopyCallback) {
      this.afterCopyCallback($target);
    }
  }

  copyTextToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    document.body.appendChild(textarea);
    textarea.select();
    try {
      const success = document.execCommand('copy');
      document.body.removeChild(textarea);
      return success;
    } catch (err) {
      document.body.removeChild(textarea);
      return false;
    }
  }
}
