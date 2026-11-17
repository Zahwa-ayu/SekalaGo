@php
    $isCar  = $type === 'cars';
    $uid    = 'rp' . substr(md5(uniqid($startName, true)), 0, 8);
    $i18n   = [
        'placeholder' => __($isCar ? 'ui.calendar_placeholder_cars' : 'ui.calendar_placeholder_hotels'),
        'pickStart'   => __($isCar ? 'ui.calendar_pick_start_cars' : 'ui.calendar_pick_start_hotels'),
        'pickEnd'     => __($isCar ? 'ui.calendar_pick_end_cars' : 'ui.calendar_pick_end_hotels'),
        'unit'        => __($isCar ? 'ui.calendar_unit_cars' : 'ui.calendar_unit_hotels'),
        'max'         => __('ui.calendar_max'),
    ];
@endphp

<div data-range-picker class="rp-root {{ $class ?? '' }}"
     data-type="{{ $type }}" data-today="{{ today()->toDateString() }}" data-max="30"
     data-prices-url="{{ route('calendar.prices') }}" data-i18n="{{ json_encode($i18n) }}">

    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-calendar-days text-sagara-blue"></i> {{ $label }}
    </label>

    <button type="button" class="rp-field" data-rp-field aria-haspopup="dialog" aria-expanded="false">
        <span data-rp-label class="rp-label"></span>
        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
    </button>

    <input type="hidden" name="{{ $startName }}" value="{{ $start }}" data-rp-start>
    <input type="hidden" name="{{ $endName }}" value="{{ $end }}" data-rp-end>

    <div class="rp-backdrop" data-rp-backdrop hidden></div>

    <div class="rp-panel" data-rp-panel role="dialog" aria-label="{{ __($isCar ? 'ui.calendar_title_cars' : 'ui.calendar_title_hotels') }}" hidden>
        <div class="rp-head">
            <div class="rp-title">{{ __($isCar ? 'ui.calendar_title_cars' : 'ui.calendar_title_hotels') }}</div>
            <button type="button" class="rp-nav" data-rp-prev aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="rp-nav" data-rp-next aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
            <button type="button" class="rp-close" data-rp-close aria-label="{{ __('ui.calendar_close') }}"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="rp-months" data-rp-months></div>

        <div class="rp-foot">
            <div>
                <div class="rp-hint" data-rp-hint></div>
                <div class="rp-legend" data-rp-legend></div>
            </div>
            <button type="button" class="rp-reset" data-rp-reset>{{ __('ui.calendar_reset') }}</button>
        </div>
    </div>
</div>

@once
    <style>
        .rp-root { position: relative; }
        .rp-field { display: flex; align-items: center; justify-content: space-between; gap: .5rem; width: 100%; text-align: left;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: .8rem .75rem; font-weight: 600;
            font-size: .875rem; color: #1e293b; cursor: pointer; transition: border-color .15s, background .15s; }
        .rp-field:hover, .rp-root.is-open .rp-field { border-color: #0ea5e9; background: #fff; }
        .rp-label.rp-placeholder { color: #94a3b8; font-weight: 500; }

        .rp-panel { position: fixed; z-index: 70; background: #fff; border: 1px solid #e2e8f0; border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .28); padding: 1rem; width: max-content; max-width: calc(100vw - 1.5rem); color: #0f172a; }
        .rp-head { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
        .rp-title { flex: 1; font-weight: 800; font-size: .95rem; }
        .rp-nav, .rp-close { width: 2rem; height: 2rem; border-radius: .6rem; border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; }
        .rp-nav:hover, .rp-close:hover { background: #f0f9ff; color: #0284c7; }
        .rp-nav:disabled { opacity: .35; cursor: not-allowed; }
        .rp-close { display: none; }

        .rp-months { display: flex; gap: 1.5rem; }
        .rp-month { width: 19.5rem; }
        .rp-month-title { text-align: center; font-weight: 800; font-size: .875rem; margin-bottom: .5rem; text-transform: capitalize; }
        .rp-week, .rp-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        .rp-week span { text-align: center; font-size: .65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding-bottom: .25rem; }
        .rp-grid { gap: 2px 0; }

        .rp-day { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 3rem;
            border: 0; border-radius: .6rem; background: transparent; cursor: pointer; line-height: 1.15; color: #0f172a; }
        .rp-day:hover:not(:disabled):not(.is-start):not(.is-end) { background: #f0f9ff; }
        .rp-day:disabled { color: #cbd5e1; cursor: not-allowed; }
        .rp-n { font-weight: 700; font-size: .875rem; }
        .rp-p { font-size: .625rem; color: #64748b; min-height: .8rem; }
        .rp-day:disabled .rp-p { color: #e2e8f0; }
        .rp-day.is-low:not(.is-start):not(.is-end) .rp-p { color: #16a34a; font-weight: 700; }
        .rp-day.is-dash .rp-p { color: #cbd5e1; }
        .rp-day.is-in { background: #e0f2fe; border-radius: 0; }
        .rp-day.is-preview { background: #bae6fd; border-radius: 0 .6rem .6rem 0; }
        .rp-day.is-start, .rp-day.is-end { background: #0ea5e9; color: #fff; }
        .rp-day.is-start .rp-p, .rp-day.is-end .rp-p { color: #e0f2fe; }
        .rp-day.is-start { border-radius: .6rem 0 0 .6rem; }
        .rp-day.is-end { border-radius: 0 .6rem .6rem 0; }
        .rp-day.is-start.is-end, .rp-day.is-start:last-child { border-radius: .6rem; }

        .rp-foot { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #f1f5f9; }
        .rp-hint { font-size: .8rem; font-weight: 700; color: #0f172a; }
        .rp-legend { font-size: .7rem; color: #94a3b8; margin-top: .15rem; }
        .rp-reset { font-size: .75rem; font-weight: 700; color: #0284c7; background: none; border: 0; cursor: pointer; white-space: nowrap; }
        .rp-reset:hover { text-decoration: underline; }

        @media (max-width: 767px) {
            .rp-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); z-index: 65; }
            .rp-panel { left: 0 !important; right: 0; bottom: 0; top: auto !important; width: auto; max-width: none;
                border-radius: 1.25rem 1.25rem 0 0; max-height: 88vh; overflow-y: auto; }
            .rp-month { width: 100%; }
            .rp-close { display: block; }
        }
        @media (min-width: 768px) { .rp-backdrop { display: none !important; } }
    </style>

    @push('scripts')
        <script>
            (function () {
                const LANG = document.documentElement.lang || 'id';
                const pad = (n) => String(n).padStart(2, '0');
                const fmt = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
                const parse = (s) => { const p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); };
                const addDays = (d, n) => new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
                const firstOfMonth = (d) => new Date(d.getFullYear(), d.getMonth(), 1);
                const addMonths = (d, n) => new Date(d.getFullYear(), d.getMonth() + n, 1);
                const monthKey = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1);
                const diff = (a, b) => Math.round((parse(b) - parse(a)) / 86400000);
                const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text !== undefined) e.textContent = text; return e; };

                const monthFmt = new Intl.DateTimeFormat(LANG, { month: 'long', year: 'numeric' });
                const numberFmt = new Intl.NumberFormat(LANG);
                const weekdays = Array.from({ length: 7 }, (_, i) =>
                    new Intl.DateTimeFormat(LANG, { weekday: 'short' }).format(new Date(2024, 0, 1 + i)));   // 1 Jan 2024 = Senin

                document.querySelectorAll('[data-range-picker]').forEach(init);

                function init(root) {
                    const type = root.dataset.type;
                    const today = root.dataset.today;
                    const MAX = parseInt(root.dataset.max, 10) || 30;
                    const url = root.dataset.pricesUrl;
                    const t = JSON.parse(root.dataset.i18n);
                    const form = root.closest('form');
                    const field = root.querySelector('[data-rp-field]');
                    const panel = root.querySelector('[data-rp-panel]');
                    const backdrop = root.querySelector('[data-rp-backdrop]');
                    const monthsEl = root.querySelector('[data-rp-months]');
                    const hintEl = root.querySelector('[data-rp-hint]');
                    const legendEl = root.querySelector('[data-rp-legend]');
                    const labelEl = root.querySelector('[data-rp-label]');
                    const inStart = root.querySelector('[data-rp-start]');
                    const inEnd = root.querySelector('[data-rp-end]');
                    const prevBtn = root.querySelector('[data-rp-prev]');
                    const maxDate = fmt(addDays(parse(today), 365));
                    const thisMonth = firstOfMonth(parse(today));

                    let start = inStart.value || null;
                    let end = inEnd.value || null;
                    let hover = null;
                    let view = start ? firstOfMonth(parse(start)) : thisMonth;
                    if (view < thisMonth) view = thisMonth;
                    let cells = [];
                    const store = {};

                    const monthsShown = () => (window.matchMedia('(min-width: 768px)').matches ? 2 : 1);
                    const cityField = () => (form ? form.querySelector('[name="city"]') : null);
                    const cityKey = () => ((cityField() && cityField().value) || '').trim().toLowerCase();
                    const current = () => (store[cityKey()] = store[cityKey()] || { prices: {}, fetched: new Set(), legend: '' });

                    function shortDate(s) {
                        const d = parse(s);
                        const withYear = d.getFullYear() !== parse(today).getFullYear();
                        return new Intl.DateTimeFormat(LANG, { weekday: 'short', day: 'numeric', month: 'short', ...(withYear ? { year: 'numeric' } : {}) }).format(d);
                    }

                    function updateLabel() {
                        if (start && end) {
                            labelEl.textContent = shortDate(start) + ' \u2013 ' + shortDate(end) + ' \u00B7 ' + diff(start, end) + ' ' + t.unit;
                            labelEl.classList.remove('rp-placeholder');
                        } else {
                            labelEl.textContent = t.placeholder;
                            labelEl.classList.add('rp-placeholder');
                        }
                    }

                    function commit() {
                        inStart.value = start || '';
                        inEnd.value = end || '';
                        updateLabel();
                        inStart.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    function buildMonth(first) {
                        const wrap = el('div', 'rp-month');
                        wrap.appendChild(el('div', 'rp-month-title', monthFmt.format(first)));

                        const week = el('div', 'rp-week');
                        weekdays.forEach((w) => week.appendChild(el('span', '', w)));
                        wrap.appendChild(week);

                        const grid = el('div', 'rp-grid');
                        const offset = (first.getDay() + 6) % 7;
                        for (let i = 0; i < offset; i++) grid.appendChild(el('span', 'rp-blank'));

                        const days = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
                        for (let day = 1; day <= days; day++) {
                            const ds = fmt(new Date(first.getFullYear(), first.getMonth(), day));
                            const btn = el('button', 'rp-day');
                            btn.type = 'button';
                            const price = el('span', 'rp-p', '');
                            btn.append(el('span', 'rp-n', String(day)), price);
                            if (ds < today || ds > maxDate) btn.disabled = true;
                            btn.addEventListener('click', () => pick(ds));
                            btn.addEventListener('mouseenter', () => { if (start && !end) { hover = ds; paintRange(); } });
                            grid.appendChild(btn);
                            cells.push({ ds, btn, price, month: monthKey(first) });
                        }

                        wrap.appendChild(grid);
                        return wrap;
                    }

                    function render() {
                        monthsEl.replaceChildren();
                        cells = [];
                        for (let i = 0; i < monthsShown(); i++) monthsEl.appendChild(buildMonth(addMonths(view, i)));
                        prevBtn.disabled = view <= thisMonth;
                        paintRange();
                        paintPrices();
                        ensurePrices();
                        place();
                    }

                    function paintRange() {
                        const preview = !end && start && hover && hover > start && diff(start, hover) <= MAX ? hover : null;
                        const last = end || preview;

                        cells.forEach(({ ds, btn }) => {
                            btn.classList.toggle('is-start', ds === start);
                            btn.classList.toggle('is-end', ds === end);
                            btn.classList.toggle('is-in', !!(start && last && ds > start && ds < last));
                            btn.classList.toggle('is-preview', !!preview && ds === preview);
                        });

                        hintEl.textContent = !start ? t.pickStart : (!end ? t.pickEnd : diff(start, end) + ' ' + t.unit);
                    }

                    function paintPrices() {
                        const s = current();
                        let min = Infinity;

                        cells.forEach(({ ds, btn }) => {
                            const v = s.prices[ds];
                            if (v !== undefined && !btn.disabled && v < min) min = v;
                        });

                        cells.forEach(({ ds, btn, price, month }) => {
                            const v = s.prices[ds];
                            const known = s.fetched.has(month);
                            price.textContent = v !== undefined ? numberFmt.format(v) : (known && !btn.disabled ? '\u2013' : '');
                            btn.classList.toggle('is-low', v !== undefined && v === min);
                            btn.classList.toggle('is-dash', v === undefined && known);
                        });

                        legendEl.textContent = s.legend || '';
                    }

                    async function ensurePrices() {
                        const s = current();
                        const key = cityKey();
                        const shown = [];
                        for (let i = 0; i < monthsShown(); i++) shown.push(addMonths(view, i));

                        const missing = shown.filter((m) => !s.fetched.has(monthKey(m)));
                        if (!missing.length) return;

                        let from = fmt(missing[0]);
                        if (from < today) from = today;
                        const lastMonth = missing[missing.length - 1];
                        const to = fmt(new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0));

                        const u = new URL(url, window.location.origin);
                        u.search = new URLSearchParams({ type, city: cityField() ? cityField().value.trim() : '', from, to }).toString();

                        try {
                            const res = await fetch(u, { headers: { Accept: 'application/json' } });
                            if (!res.ok) return;
                            const data = await res.json();
                            Object.assign(s.prices, data.prices);
                            s.legend = data.legend;
                            missing.forEach((m) => s.fetched.add(monthKey(m)));
                        } catch (e) {
                            return;
                        }

                        if (key === cityKey()) paintPrices();
                    }

                    function pick(ds) {
                        hover = null;

                        if (!start || end) {
                            start = ds;
                            end = null;
                        } else if (ds <= start) {
                            start = ds;
                        } else if (diff(start, ds) > MAX) {
                            hintEl.textContent = t.max.replace(':count', MAX).replace(':unit', t.unit);
                            return;
                        } else {
                            end = ds;
                            commit();
                            paintRange();
                            close();
                            return;
                        }

                        paintRange();
                    }

                    // Panel memakai posisi tetap supaya tidak terpotong kartu pencarian di Beranda
                    function place() {
                        if (panel.hidden || window.matchMedia('(max-width: 767px)').matches) {
                            panel.style.top = '';
                            panel.style.left = '';
                            return;
                        }
                        const rect = field.getBoundingClientRect();
                        const width = panel.offsetWidth;
                        const height = panel.offsetHeight;
                        let left = Math.min(rect.left, window.innerWidth - width - 12);
                        let top = rect.bottom + 8;
                        if (top + height > window.innerHeight - 8 && rect.top - height - 8 > 8) top = rect.top - height - 8;
                        panel.style.left = Math.max(12, left) + 'px';
                        panel.style.top = top + 'px';
                    }

                    function open() {
                        view = start ? firstOfMonth(parse(start)) : thisMonth;
                        if (view < thisMonth) view = thisMonth;
                        panel.hidden = false;
                        backdrop.hidden = false;
                        root.classList.add('is-open');
                        field.setAttribute('aria-expanded', 'true');
                        render();
                    }

                    function close() {
                        panel.hidden = true;
                        backdrop.hidden = true;
                        root.classList.remove('is-open');
                        field.setAttribute('aria-expanded', 'false');
                        hover = null;
                    }

                    field.addEventListener('click', () => (panel.hidden ? open() : close()));
                    backdrop.addEventListener('click', close);
                    root.querySelector('[data-rp-close]').addEventListener('click', close);
                    prevBtn.addEventListener('click', () => { view = addMonths(view, -1); render(); });
                    root.querySelector('[data-rp-next]').addEventListener('click', () => { view = addMonths(view, 1); render(); });
                    root.querySelector('[data-rp-reset]').addEventListener('click', () => { start = null; end = null; hover = null; commit(); render(); });
                    monthsEl.addEventListener('mouseleave', () => { hover = null; paintRange(); });

                    document.addEventListener('click', (e) => { if (!panel.hidden && !root.contains(e.target)) close(); });
                    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) close(); });
                    window.addEventListener('resize', () => { if (!panel.hidden) render(); });
                    window.addEventListener('scroll', () => { if (!panel.hidden) place(); }, true);

                    // Harga mengikuti kota yang diketik
                    let typing;
                    const city = cityField();
                    if (city) {
                        const refresh = () => { if (!panel.hidden) { paintPrices(); ensurePrices(); } };
                        city.addEventListener('change', refresh);
                        city.addEventListener('input', () => { clearTimeout(typing); typing = setTimeout(refresh, 500); });
                    }

                    // Form tidak terkirim sebelum rentang tanggal lengkap
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            if (!inStart.value || !inEnd.value) {
                                e.preventDefault();
                                if (panel.hidden) open();
                            }
                        });
                    }

                    updateLabel();
                }
            })();
        </script>
    @endpush
@endonce