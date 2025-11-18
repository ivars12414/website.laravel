class BirthDate {
  // ==== Статическая часть (инициализация / авто-поиск / наблюдение) ====
  static _instances = new WeakMap();
  static _observer = null;
  static _cfg = {
    containerSelector: '[data-birthdate]',  // контейнер блока даты
    daySel: 'select[name="birth_day"]',
    monthSel: 'select[name="birth_month"]',
    yearSel: 'select[name="birth_year"]',
    minAge: 18,
    monthIsOneBased: true,
    yearsBack: 100,                          // рендер лет: [текущий - yearsBack .. текущий]
    monthNames: (window.MONTH_NAMES || null),// опционально: ['Январь', ...]
    onError: (msg, ctx) => {                 // ctx: {container, day, month, year}
      // лаконичный вывод ошибки под блоком
      let holder = ctx.container.querySelector('.birthdate-error');
      if (!holder) {
        holder = document.createElement('div');
        holder.className = 'birthdate-error';
        holder.style.color = '#c0392b';
        holder.style.fontSize = '12px';
        holder.style.marginTop = '6px';
        ctx.container.appendChild(holder);
      }
      holder.textContent = msg;
    },
    clearError: (ctx) => {
      const holder = ctx.container.querySelector('.birthdate-error');
      if (holder) holder.textContent = '';
    },
  };

  static configure(partialCfg = {}) {
    this._cfg = { ...this._cfg, ...partialCfg };
  }

  // Одноразовый прогон: найти/проинициализировать все контейнеры
  static refresh(root = document) {
    const cfg = this._cfg;
    const containers = root.querySelectorAll(cfg.containerSelector);
    containers.forEach(c => {
      if (!this._instances.has(c)) {
        const inst = new BirthDate(c, cfg);
        this._instances.set(c, inst);
      } else {
        this._instances.get(c).touch(); // повторная валидация/дорисовка
      }
    });
  }

  // Включить слежение за DOM (для AJAX)
  static observe(root = document) {
    if (this._observer) return; // уже включён
    this.refresh(root);
    this._observer = new MutationObserver(() => this.refresh(root));
    this._observer.observe(root, { childList: true, subtree: true });
  }

  // Отключить слежение
  static disconnect() {
    if (this._observer) this._observer.disconnect();
    this._observer = null;
  }

  // ==== Экземпляр ====
  constructor(container, cfg) {
    this.cfg = cfg;
    this.container = container;

    // Найти/создать селекты
    this.day = container.querySelector(cfg.daySel) || this._createSelect('birth_day');
    this.month = container.querySelector(cfg.monthSel) || this._createSelect('birth_month');
    this.year = container.querySelector(cfg.yearSel) || this._createSelect('birth_year');

    // Если селекты пустые — отрисуем
    this._ensurePopulated();

    // Слушатели
    ['change', 'input'].forEach(ev => {
      this.day.addEventListener(ev, () => this._onAnyChange());
      this.month.addEventListener(ev, () => this._onAnyChange());
      this.year.addEventListener(ev, () => this._onAnyChange());
    });

    // Первый прогон
    this._onAnyChange(true);
  }

  touch() {
    this._onAnyChange(true);
  }

  _createSelect(name) {
    const wrap = document.createElement('div');
    wrap.className = 'select';
    const sel = document.createElement('select');
    sel.name = name;
    wrap.appendChild(sel);
    // куда вставить: пробуем .form__row, иначе — в контейнер
    const row = this.container.querySelector('.form__row');
    (row || this.container).appendChild(wrap);
    return sel;
  }

  _ensurePopulated() {
    // Не трогаем, если уже есть реальные options (кроме единственного placeholder)
    const ensure = (sel, placeholder) => {
      const realOptions = Array.from(sel.options).filter(o => o.value !== '');
      if (realOptions.length > 0) return; // уже заполнено на сервере
      sel.innerHTML = '';
      const ph = document.createElement('option');
      ph.value = '';
      ph.textContent = placeholder;
      ph.disabled = true;
      ph.selected = true;
      sel.appendChild(ph);
    };

    ensure(this.day, 'DD');
    ensure(this.month, 'MM');
    ensure(this.year, 'YYYY');

    // Если пусто — нарисуем значения
    if (this.day.options.length <= 1) this._renderDays(31);
    if (this.month.options.length <= 1) this._renderMonths();
    if (this.year.options.length <= 1) this._renderYears();
  }

  _renderDays(max = 31) {
    for (let i = 1; i <= max; i++) {
      const opt = document.createElement('option');
      opt.value = String(i);
      opt.textContent = String(i).padStart(2, '0');
      this.day.appendChild(opt);
    }
  }

  _renderMonths() {
    const names = this.cfg.monthNames;
    for (let i = 1; i <= 12; i++) {
      const opt = document.createElement('option');
      const idx0 = i - 1;
      opt.value = String(this.cfg.monthIsOneBased ? i : idx0);
      opt.textContent = names && names[idx0] ? names[idx0] : String(i).padStart(2, '0');
      this.month.appendChild(opt);
    }
  }

  _renderYears() {
    const now = new Date();
    const yStart = now.getFullYear() - this.cfg.yearsBack;
    const yEnd = now.getFullYear();
    for (let y = yStart; y <= yEnd; y++) {
      const opt = document.createElement('option');
      opt.value = String(y);
      opt.textContent = String(y);
      this.year.appendChild(opt);
    }
  }

  _monthIndex0() {
    if (!this.month.value) return null;
    const raw = parseInt(this.month.value, 10);
    return this.cfg.monthIsOneBased ? (raw - 1) : raw; // 0..11
  }

  _daysInMonth(y, m0) {
    return new Date(y, m0 + 1, 0).getDate();
  }

  _candidateDate() {
    const d = parseInt(this.day.value, 10);
    const y = parseInt(this.year.value, 10);
    const m0 = this._monthIndex0();
    if (!d || !y || m0 == null) return null;
    return new Date(y, m0, d);
  }

  _validExistingDate(dt) {
    const d = parseInt(this.day.value, 10);
    const y = parseInt(this.year.value, 10);
    const m0 = this._monthIndex0();
    return dt && dt.getFullYear() === y && dt.getMonth() === m0 && dt.getDate() === d;
  }

  _adjustDays() {
    const y = parseInt(this.year.value, 10);
    const m0 = this._monthIndex0();
    if (!y || m0 == null) return;
    const maxD = this._daysInMonth(y, m0);

    // Если в селекте дней 1..31 отрисованы сервером/нами — просто скорректируем выбранное значение
    const d = parseInt(this.day.value, 10);
    if (d && d > maxD) this.day.value = String(maxD);
  }

  _isValidAge() {
    const dt = this._candidateDate();
    if (!this._validExistingDate(dt)) return false;
    const today = new Date();
    const minBirth = new Date(today.getFullYear() - this.cfg.minAge, today.getMonth(), today.getDate());
    return dt <= minBirth;
  }

  _errorText() {
    const dt = this._candidateDate();
    const d = parseInt(this.day.value, 10);
    const y = parseInt(this.year.value, 10);
    const m0 = this._monthIndex0();
    const monthLabel = (this.cfg.monthNames && m0 != null) ? this.cfg.monthNames[m0] : this.month.value || '';
    const dateStr = (dt && this._validExistingDate(dt)) ? `${d} ${monthLabel} ${y}` : 'некорректная дата';
    return `Возраст должен быть не менее ${this.cfg.minAge} лет (выбрано: ${dateStr}).`;
  }

  _markInvalid(state) {
    [this.day, this.month, this.year].forEach(n => n.classList.toggle('is-invalid', state));
    if (!state) this.cfg.clearError({ container: this.container, day: this.day, month: this.month, year: this.year });
  }

  _onAnyChange(firstRun = false) {
    this._adjustDays();
    const complete = Boolean(this.day.value && this.month.value && this.year.value);
    if (!complete) {
      this._markInvalid(false);
      return;
    }

    if (!this._isValidAge()) {
      this._markInvalid(true);
      // на первом прогоне без шума, дальше — показываем текст
      if (!firstRun) this.cfg.onError(this._errorText(), {
        container: this.container,
        day: this.day,
        month: this.month,
        year: this.year,
      });
    } else {
      this._markInvalid(false);
    }
  }
}