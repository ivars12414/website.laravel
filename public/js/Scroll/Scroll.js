class Scroll {
  constructor(config) {
    this.linkSelector = config.linkSelector;
    this.smoothScrollOffset = config.smoothScrollOffset;
    this.smoothScrollSpeed = config.smoothScrollSpeed;

    this.init();
  }

  init() {
    const { linkSelector, smoothScrollOffset, smoothScrollSpeed } = this;

    // Обработчик кликов на ссылки
    $(linkSelector).on('click', function(event) {
      event.preventDefault();
      let hash = this.hash;
      if ($(this).data('target')) {
        hash = $(this).data('target');
      } else if ($(this).data('scrollTo')) {
        hash = $(this).data('scrollTo');
      } else {
        history.pushState(null, null, hash);
      }
      Scroll.scrollToElement(hash, smoothScrollOffset, smoothScrollSpeed);
    });

    // Проверяем наличие якоря в URL
    if (window.location.hash) {
      const hash = window.location.hash;
      window.location.hash = '';
      $('html, body').scrollTop(0);
      setTimeout(() => {
        Scroll.scrollToElement(hash, smoothScrollOffset, smoothScrollSpeed);
        window.location.hash = hash;
      }, 0);
    }
  }

  static scrollToElement(selector, offset, speed, callback) {
    const scrollTop =
      $(selector).length > 0
        ? ($(selector).offset().top - offset)
        : 0;
    $('html, body').animate({
      scrollTop,
    }, speed, callback);
  }
}