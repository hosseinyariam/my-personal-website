/**
 * جاوااسکریپت وانیلا — بدون کتابخانه.
 *
 * تصمیم معماری: مستندات صریحاً «حداقل جاوااسکریپت» و «بدون کتابخانه‌ی
 * سنگین» را الزام کرده‌اند (معماری:1076-1089 و UI Kit:529). برای
 * منوی موبایل و کپی لینک، هیچ نیازی به React یا Alpine نیست.
 *
 * اصل Progressive Enhancement: اگر این فایل اجرا نشود، سایت هنوز
 * کار می‌کند — منوی موبایل بسته می‌ماند ولی همه‌ی مسیرها از فوتر
 * و صفحه اصلی قابل دسترسی‌اند.
 *
 * توجه: axios و bootstrap.js پیش‌فرض Laravel حذف شدند چون هیچ
 * درخواست AJAX در سایت وجود ندارد — تمام تعامل‌ها فرم‌های معمولی‌اند.
 */

(function () {
    'use strict';

    /**
     * منوی موبایل — باز/بسته با مدیریت کامل دسترس‌پذیری.
     *
     * چه چیزهایی رعایت می‌شود:
     *   - aria-expanded روی دکمه
     *   - تغییر aria-label («باز کردن» → «بستن»)
     *   - بستن با کلید Escape
     *   - بازگرداندن فوکوس به دکمه پس از بستن
     */
    function initMobileMenu() {
        const toggle = document.querySelector('[data-mobile-menu-toggle]');

        if (!toggle) {
            return;
        }

        const menu = document.getElementById('mobile-menu');
        const iconOpen = toggle.querySelector('[data-menu-icon-open]');
        const iconClose = toggle.querySelector('[data-menu-icon-close]');

        if (!menu) {
            return;
        }

        function setOpen(open) {
            menu.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'بستن منو' : 'باز کردن منو');

            if (iconOpen && iconClose) {
                iconOpen.classList.toggle('hidden', open);
                iconClose.classList.toggle('hidden', !open);
            }
        }

        toggle.addEventListener('click', function () {
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            setOpen(!isOpen);
        });

        // Escape — کاربر صفحه‌کلید نباید در منوی باز زندانی شود
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                setOpen(false);
                toggle.focus();
            }
        });

        // بستن منو با تغییر اندازه به دسکتاپ — جلوگیری از حالت ناسازگار
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768 && toggle.getAttribute('aria-expanded') === 'true') {
                setOpen(false);
            }
        });
    }

    /**
     * دکمه‌ی کپی لینک/ایمیل — با بازخورد بصری و دسترس‌پذیر.
     */
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                const value = button.getAttribute('data-copy');

                if (!value) {
                    return;
                }

                navigator.clipboard.writeText(value).then(function () {
                    const original = button.getAttribute('aria-label') || 'کپی';
                    button.setAttribute('aria-label', 'کپی شد');

                    // بازخورد موقت — نه وضعیت دائمی
                    setTimeout(function () {
                        button.setAttribute('aria-label', original);
                    }, 2000);
                }).catch(function () {
                    // اگر clipboard API در دسترس نبود (مرورگر قدیمی یا
                    // بدون HTTPS)، بازخورد نادرست نمی‌دهیم.
                });
            });
        });
    }

    /**
     * شمارشگر متن برای فرم تماس.
     *
     * چرا لازم است: پیام بیش از ۵۰۰۰ حرف رد می‌شود. بدون شمارشگر،
     * کاربر بعد از ارسال متوجه می‌شود — تجربه‌ی بدی است.
     */
    function initCharacterCounter() {
        const textarea = document.querySelector('[data-character-counter]');

        if (!textarea) {
            return;
        }

        const counter = document.getElementById('message-counter');
        const max = parseInt(textarea.getAttribute('maxlength') || '5000', 10);

        if (!counter) {
            return;
        }

        function update() {
            const length = textarea.value.length;
            counter.textContent = toPersianDigits(length) + ' از ' + toPersianDigits(max);

            const nearLimit = length > max * 0.9;
            counter.classList.toggle('text-warning', nearLimit);
            counter.classList.toggle('text-text-subtle', !nearLimit);
        }

        textarea.addEventListener('input', update);
        update();
    }

    /** ارقام فارسی برای نمایش یکدست. */
    function toPersianDigits(value) {
        return String(value).replace(/\d/g, function (digit) {
            return '۰۱۲۳۴۵۶۷۸۹'[digit];
        });
    }

    // اجرا پس از آماده‌شدن DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initMobileMenu();
            initCopyButtons();
            initCharacterCounter();
        });
    } else {
        initMobileMenu();
        initCopyButtons();
        initCharacterCounter();
    }
})();
