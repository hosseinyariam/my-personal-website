/**
 * لایه‌ی تعاملی سایت — جاوااسکریپت وانیلا، بدون فریم‌ورک (تصمیم ۹.۱ سند PRD).
 *
 * مسئولیت‌ها: تعویض تم، توست، کپی، شمارنده‌ی کاراکتر، منوی موبایل،
 * تب‌ها، نور تعقیب‌کننده‌ی موس و نمایان‌شدن هنگام اسکرول.
 * هیچ رفتاری اینجا برای «کارکرد» صفحه ضروری نیست — سایت بدون JS هم کامل کار می‌کند.
 */

const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];

const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const toFa = (v) => String(v).replace(/\d/g, (d) => FA_DIGITS[d]);

/* ── سیستم توست ─────────────────────────────────────────── */

const TOAST_ICONS = { success: 'circle-check', error: 'alert-circle', info: 'info-circle' };
const TOAST_TILES = { success: 'tile-brand', error: 'tile-deep', info: 'tile-line' };

function toast(type = 'info', title = '', message = '') {
    const box = $('#toastBox');
    if (!box) return;

    const el = document.createElement('div');
    el.className =
        'glass pointer-events-auto relative flex w-[22rem] max-w-[calc(100vw-3rem)] items-start gap-3 overflow-hidden rounded-2xl p-4 animate-toast-in';
    el.setAttribute('role', 'status');

    const tile = document.createElement('span');
    tile.className = `icon-tile ${TOAST_TILES[type] ?? TOAST_TILES.info} h-9 w-9 rounded-xl text-base`;
    tile.innerHTML = `<svg class="h-5 w-5" aria-hidden="true"><use href="/icons.svg#i-${TOAST_ICONS[type] ?? TOAST_ICONS.info}"></use></svg>`;

    const text = document.createElement('div');
    text.className = 'min-w-0 flex-1';
    if (title) {
        const t = document.createElement('p');
        t.className = 'text-sm font-bold text-heading';
        t.textContent = title; // textContent: هیچ HTMLی از ورودی تزریق نمی‌شود
        text.appendChild(t);
    }
    if (message) {
        const m = document.createElement('p');
        m.className = 'mt-0.5 text-xs leading-5 text-body';
        m.textContent = message;
        text.appendChild(m);
    }

    const close = document.createElement('button');
    close.className = 'mt-0.5 text-muted transition-colors hover:text-heading';
    close.setAttribute('aria-label', 'بستن');
    close.innerHTML = '<svg class="h-4 w-4" aria-hidden="true"><use href="/icons.svg#i-x"></use></svg>';

    const bar = document.createElement('span');
    bar.className = 'absolute bottom-0 start-0 h-0.5 bg-brand-500/70 animate-bar';

    el.append(tile, text, close, bar);
    box.appendChild(el);

    let dead = false;
    const kill = () => {
        if (dead) return;
        dead = true;
        el.classList.add('animate-toast-out');
        setTimeout(() => el.remove(), 260);
    };
    close.addEventListener('click', kill);
    setTimeout(kill, 4000);
}

/* ── تعویض تم (روشن پایه / تاریک مشتق‌شده) ───────────────── */

function applyThemeIcon(isDark) {
    $$('[data-theme-icon]').forEach((icon) => {
        icon.innerHTML = `<use href="/icons.svg#i-${isDark ? 'sun' : 'moon'}"></use>`;
    });
    $$('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute('aria-label', isDark ? 'تغییر به تم روشن' : 'تغییر به تم تاریک');
        btn.setAttribute('aria-pressed', String(isDark));
    });
}

function toggleTheme() {
    const isDark = !document.documentElement.classList.contains('dark');
    document.documentElement.classList.add('theme-anim');
    document.documentElement.classList.toggle('dark', isDark);
    applyThemeIcon(isDark);
    try {
        localStorage.setItem('hy-theme', isDark ? 'dark' : 'light');
    } catch {
        /* حالت خصوصی مرورگر — تم فقط برای همین بازدید می‌ماند */
    }
    setTimeout(() => document.documentElement.classList.remove('theme-anim'), 500);
}

applyThemeIcon(document.documentElement.classList.contains('dark'));

/* ── کپی در کلیپ‌بورد ────────────────────────────────────── */

async function copyText(value) {
    try {
        await navigator.clipboard.writeText(value);
    } catch {
        const helper = document.createElement('textarea');
        helper.value = value;
        helper.style.position = 'fixed';
        helper.style.opacity = '0';
        document.body.appendChild(helper);
        helper.select();
        try {
            document.execCommand('copy');
        } catch {
            /* مرورگر اجازه نداد — کاربر می‌تواند دستی کپی کند */
        }
        helper.remove();
    }
}

/* ── واگذاری رویدادها (Event delegation) ─────────────────── */

document.addEventListener('click', async (e) => {
    if (e.target.closest('[data-theme-toggle]')) {
        toggleTheme();
        return;
    }

    const copyBtn = e.target.closest('[data-copy]');
    if (copyBtn) {
        const value = copyBtn.dataset.copy;
        await copyText(value);
        toast('success', 'کپی شد', value.length > 44 ? `${value.slice(0, 44)}…` : value);
        return;
    }

    const toastBtn = e.target.closest('[data-toast]');
    if (toastBtn) {
        const [type, title, message] = toastBtn.dataset.toast.split('|');
        toast(type || 'info', title || '', message || '');
    }
});

/* ── منوی موبایل ─────────────────────────────────────────── */

const menuBtn = $('#menuBtn');
const mobileMenu = $('#mobileMenu');
if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
        const open = mobileMenu.classList.toggle('hidden') === false;
        mobileMenu.classList.toggle('flex', open);
        menuBtn.setAttribute('aria-expanded', String(open));
    });
    $$('a', mobileMenu).forEach((a) =>
        a.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
            menuBtn.setAttribute('aria-expanded', 'false');
        })
    );
}

/* ── تب‌ها (مسئله / راه‌حل / امکانات) ────────────────────── */

$$('[data-tabs]').forEach((box) => {
    const tabs = $$('.tab', box);
    const panels = $$('[data-panel]', box);

    const select = (tab) => {
        tabs.forEach((t) => {
            const on = t === tab;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
        });
        panels.forEach((p) => p.classList.toggle('hidden', p.dataset.panel !== tab.dataset.tab));
    };

    box.addEventListener('click', (e) => {
        const tab = e.target.closest('.tab');
        if (tab) select(tab);
    });

    // ناوبری کیبورد در نوار تب — الزام WCAG
    box.addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
        const current = tabs.indexOf(document.activeElement);
        if (current === -1) return;
        e.preventDefault();
        // RTL: فلش چپ یعنی «بعدی»
        const step = e.key === 'ArrowLeft' ? 1 : -1;
        const next = tabs[(current + step + tabs.length) % tabs.length];
        next.focus();
        select(next);
    });
});

/* ── شمارنده‌ی کاراکتر ───────────────────────────────────── */

$$('[data-counter-for]').forEach((counter) => {
    const field = document.getElementById(counter.dataset.counterFor);
    if (!field) return;
    const max = field.getAttribute('maxlength') ?? '';
    const render = () => {
        counter.textContent = `${toFa(field.value.length)} / ${toFa(max)}`;
    };
    field.addEventListener('input', render);
    render();
});

/* ── نمایان‌شدن هنگام اسکرول ─────────────────────────────── */

const reveals = $$('[data-reveal]');
if (reveals.length && 'IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const io = new IntersectionObserver(
        (entries) =>
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.remove('opacity-0');
                entry.target.classList.add('animate-fade-up');
                io.unobserve(entry.target);
            }),
        { threshold: 0.05 }
    );
    reveals.forEach((el) => {
        el.classList.add('opacity-0');
        io.observe(el);
    });
}

/* ── نور گرم تعقیب‌کننده‌ی موس روی مش (فقط pointer:fine) ── */

const cursorLight = $('#cursorLight');
if (cursorLight && matchMedia('(pointer:fine)').matches && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let tx = innerWidth * 0.6;
    let ty = innerHeight * 0.35;
    let cx = tx;
    let cy = ty;

    addEventListener('pointermove', (e) => {
        tx = e.clientX;
        ty = e.clientY;
    });

    (function loop() {
        cx += (tx - cx) * 0.055;
        cy += (ty - cy) * 0.055;
        cursorLight.style.transform = `translate(${cx - 288}px, ${cy - 288}px)`;
        requestAnimationFrame(loop);
    })();
} else if (cursorLight) {
    cursorLight.remove();
}

/* ── توست‌های سمت سرور (فلش سشن) ─────────────────────────── */

$$('[data-server-toast]').forEach((node) => {
    const { type = 'info', title = '', message = '' } = node.dataset;
    toast(type, title, message);
    node.remove();
});

/* در دسترس برای اسکریپت‌های درون‌صفحه‌ای (مثل رویدادهای Matomo) */
window.hy = { toast, copyText, toFa };
