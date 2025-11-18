# FormSubmit

Класс `FormSubmit` предназначен для упрощения работы с формами в веб-приложениях, позволяя отправлять данные через AJAX
с расширенными возможностями управления алертами и анимациями.

## Особенности

- Поддержка AJAX-отправки форм.
- Автоматическое создание блоков для алертов.
- Кастомизация алертов (классы, позиционирование, анимации).
- Поддержка мультипарт-форм (для загрузки файлов).
- Автоматическое скрытие алертов.

## Установка

Вставьте следующий скрипт в ваш HTML файл или загрузите его как внешний JavaScript-файл в ваш проект.

```html

<script src="path/to/FormSubmit.js"></script>
```

## Примеры использования

Базовая отправка формы

```javascript
new FormSubmit({
  ajaxUrl: '/path/to/server',
  formSelector: '#myForm',
  type: 'POST',
  dataType: 'JSON',
  successCallback: function (response) {
    console.log('Успех:', response);
  },
  errorCallback: function (response) {
    console.error('Ошибка:', response);
  }
});
```

## Расширенная отправка с алертами и файлами

```javascript
new FormSubmit({
  ajaxUrl: '/path/to/server',
  formSelector: '#myFormWithFiles',
  type: 'POST',
  dataType: 'JSON',
  multipart: true,
  createDefaultAlerts: true,
  alertPosition: 'before',
  successClass: 'alert-success',
  errorClass: 'alert-danger',
  alertTimeout: 5000,
  fadeInTime: 500,
  fadeOutTime: 500,
  successCallback: function (response) {
    alert('Форма успешно отправлена.');
  },
  errorCallback: function (response) {
    alert('Ошибка при отправке формы.');
  }
});
```

## Возможные улучшения

Поддержка адаптивности для разных устройств.

- Легкая интеграция с фреймворками веб-разработки.
- Дополнительные настройки анимаций.

## Лицензия

Указать тип лицензии, под которой распространяется ваш код.