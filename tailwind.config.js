/**
 * توکن‌های دیزاین‌سیستم — منبع حقیقت: UI Kit «گلس روشن» (بخش ۸.۱ سند PRD).
 *
 * قاعده‌ی سخت‌گیرانه: هیچ مقدار بصری خامی بیرون از این فایل مجاز نیست.
 * نقش‌های معنایی از متغیرهای CSS می‌آیند تا تعویض تم فقط با عوض‌شدن ۸ متغیر انجام شود.
 */
export default {
    darkMode: 'class',

    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                /* ── پالت خام (Primitive) ── */
                ink: {
                    950: '#0E1014', 900: '#16181D', 800: '#2A2E37', 700: '#3E4350',
                    600: '#565B67', 500: '#6B6F79', 400: '#878B95', 300: '#A5A9B2',
                    200: '#C6C9CF', 100: '#E4E6E9',
                },
                // Brand 600 نسبت به کیت سه واحد تیره‌تر است تا متن سفیدِ روی آن
                // به کنتراست ۴٫۶۷:۱ برسد و معیار WCAG AA را پاس کند (D24).
                brand: {
                    50: '#FFF4ED', 100: '#FFE3D1', 200: '#FFC19E', 300: '#FF9F6B',
                    400: '#F97A49', 500: '#F2542D', 600: '#D33F1D', 700: '#B23417',
                },

                /* ── نقش‌های معنایی (Semantic) ── */
                canvas: 'rgb(var(--c-canvas) / <alpha-value>)',
                surface: 'rgb(var(--c-surface) / <alpha-value>)',
                line: 'rgb(var(--c-line) / <alpha-value>)',
                heading: 'rgb(var(--c-heading) / <alpha-value>)',
                body: 'rgb(var(--c-body) / <alpha-value>)',
                muted: 'rgb(var(--c-muted) / <alpha-value>)',
                accent: 'rgb(var(--c-accent) / <alpha-value>)',
                'accent-deep': 'rgb(var(--c-accent-deep) / <alpha-value>)',
            },

            fontFamily: {
                sans: ['Vazirmatn', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'Vazirmatn', 'monospace'],
            },

            fontSize: {
                display: ['clamp(2.1rem, 4.5vw, 3rem)', { lineHeight: '1.22', fontWeight: '900' }],
                h1: ['2rem', { lineHeight: '1.35', fontWeight: '800' }],
                h2: ['1.5rem', { lineHeight: '1.45', fontWeight: '800' }],
                h3: ['1.125rem', { lineHeight: '1.55', fontWeight: '700' }],
            },

            boxShadow: {
                soft: '0 1px 2px rgba(22,24,29,0.04), 0 4px 12px -4px rgba(22,24,29,0.06)',
                lift: '0 2px 4px rgba(22,24,29,0.04), 0 18px 36px -16px rgba(22,24,29,0.16)',
                glass: '0 1px 2px rgba(22,24,29,0.03), 0 16px 40px -20px rgba(22,24,29,0.14), inset 0 1px 0 rgba(255,255,255,0.6)',
                glow: '0 10px 22px -8px rgba(242,84,45,0.45)',
            },

            keyframes: {
                'fade-up': { from: { opacity: '0', transform: 'translateY(20px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                'toast-in': { from: { opacity: '0', transform: 'translateY(14px) scale(.96)' }, to: { opacity: '1', transform: 'translateY(0) scale(1)' } },
                'toast-out': { from: { opacity: '1' }, to: { opacity: '0', transform: 'translateY(10px) scale(.96)' } },
                'modal-in': { from: { opacity: '0', transform: 'translateY(24px) scale(.95)' }, to: { opacity: '1', transform: 'translateY(0) scale(1)' } },
                'float-slow': { '0%,100%': { transform: 'translate(0,0) scale(1)' }, '50%': { transform: 'translate(3%,-5%) scale(1.1)' } },
                bar: { from: { width: '100%' }, to: { width: '0%' } },
            },

            animation: {
                'fade-up': 'fade-up .7s cubic-bezier(.22,1,.36,1) both',
                'toast-in': 'toast-in .4s cubic-bezier(.22,1,.36,1) both',
                'toast-out': 'toast-out .25s ease both',
                'modal-in': 'modal-in .4s cubic-bezier(.22,1,.36,1) both',
                'float-slow': 'float-slow 18s ease-in-out infinite',
                bar: 'bar 4s linear forwards',
            },
        },
    },

    plugins: [],
};
