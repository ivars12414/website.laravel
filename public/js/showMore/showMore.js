(function($) {
  /**
   * у элементов списка должен быть data-list-item
   *
   * у контейнера указывается data-show-more-button='#showMoreBtn', где #showMoreBtn это селектор кнопки
   *
   * @param options numToShow - кол-во элементов, которое показывать сначала
   *
   *                numToLoad - сколько подгружать по клику
   *
   *                showAll - по клику подгружать сразу всё
   *
   *                ajaxUrl - URL для AJAX-запросов
   *
   *                ajaxData - дополнительные данные для AJAX-запроса (например, параметры)
   * */
  $.fn.showMore = function(options) {
    const settings = $.extend({
      numToShow: 12,
      numToLoad: 24,
      showAll: false,
      ajaxUrl: null,
      ajaxData: {},
    }, options);

    return this.each(function() {
      const container = $(this);
      const elements = container.find('[data-list-item]');
      const showMoreButton = $($(this).data('show-more-button'));
      let offset = settings.numToShow + 1;

      elements.slice(settings.numToShow).hide();

      if (!settings.ajaxUrl) {
        if (elements.length > settings.numToShow) {
          showMoreButton.show();
        } else {
          showMoreButton.hide();
        }
      }

      showMoreButton.on('click', function(e) {
        e.preventDefault();

        if (settings.showAll) {
          elements.fadeIn();
          showMoreButton.hide();
        } else {
          if (settings.ajaxUrl) {
            // Если задан URL для AJAX-запросов
            $.ajax({
              url: settings.ajaxUrl,
              method: 'POST',
              data: $.extend({}, settings.ajaxData, { offset: offset, limit: settings.numToLoad }),
              success: function(data) {
                const newElements = $(data.html); // Предполагается, что сервер возвращает HTML элементов
                container.append(newElements);
                newElements.hide().fadeIn();
                offset += settings.numToLoad;

                // Скрываем кнопку, если больше элементов нет
                if (!data.hasMore || newElements.length < settings.numToLoad) {
                  showMoreButton.hide();
                }
              },
              error: function(xhr, status, error) {
                console.error('Ошибка AJAX-запроса:', status, error);
              },
            });
          } else {
            // Если AJAX не используется, показываем элементы, которые уже есть
            var showing = elements.filter(':visible').length;
            elements.slice(showing - 1, showing + settings.numToLoad).removeClass('hidden').fadeIn();
            var nowShowing = elements.filter(':visible').length;
            if (nowShowing >= elements.length) {
              showMoreButton.hide();
            }
          }
        }
      });
    });
  };
}(jQuery));