{{--
  صفحه‌ی خطای ۵۰۰ — «خطای سرور».

  ── چرا هیچ جزئیات فنی نشان داده نمی‌شود ──
  پیام استثنا، stack trace و نام فایل، هم اطلاعات حساس افشا می‌کند
  و هم برای کاربر بی‌معناست. کاربر فقط باید بداند خطایی رخ داده و
  می‌تواند دوباره تلاش کند. جزئیات در لاگ سرور ثبت می‌شود.

  ── چرا مستقل از layout ──
  در خطای ۵۰۰ احتمال اینکه خودِ layout هم به دیتابیس نیاز داشته باشد
  و شکست بخورد زیاد است. HTML حداقلی، این خطر را حذف می‌کند.
--}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطای سرور | {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --color-bg: #ffffff;
            --color-text: #0f172a;
            --color-text-muted: #475569;
            --color-accent: #2563eb;
            --color-accent-hover: #1d4ed8;
            --radius-control: 0.625rem;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 2rem 1.25rem;
            text-align: center;
            background-color: var(--color-bg);
            color: var(--color-text);
            font-family: 'Vazirmatn', ui-sans-serif, system-ui, sans-serif;
            line-height: 1.85;
        }

        .status { font-size: 4.5rem; font-weight: 700; line-height: 1; color: var(--color-accent); }

        h1 { margin: 0; font-size: 1.5rem; }

        p { margin: 0; max-width: 28rem; color: var(--color-text-muted); }

        a.home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            margin-top: 0.5rem;
            padding-inline: 1.25rem;
            border-radius: var(--radius-control);
            background-color: var(--color-accent);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
        }

        a.home:hover { background-color: var(--color-accent-hover); }

        :focus-visible { outline: 2px solid var(--color-accent); outline-offset: 2px; }
    </style>
</head>
<body>
    <p class="status num-fa" aria-hidden="true">۵۰۰</p>

    <h1>خطای سرور</h1>

    {{-- هیچ جزئیات فنی: نه پیام استثنا، نه stack trace. --}}
    <p>خطایی غیرمنتظره در سرور رخ داد. مشکل ثبت شد؛ لطفاً کمی بعد دوباره تلاش کنید.</p>

    <a href="/" class="home">بازگشت به صفحه اصلی</a>
</body>
</html>
