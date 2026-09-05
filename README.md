# hosseinyariam.ir

وب‌سایت شخصی و زیرساخت ویترین پلتفرم‌ها — پیاده‌سازی سند PRD نسخه‌ی ۱.۰.

> **اصل حاکم:** محتوا از پنل می‌آید، نه از کد. برای تغییر هر متن، تصویر یا افزودن
> پلتفرم جدید نیازی به دیپلوی نیست.

---

## استک

| لایه | فناوری |
|---|---|
| بک‌اند | PHP 8.2+ · Laravel 12 |
| پنل مدیریت | Filament 3.3 |
| فرانت‌اند | Blade · Tailwind CSS 3 · جاوااسکریپت وانیلا (بدون SPA) |
| دیتابیس | MySQL 8 / MariaDB 10.6+ (`utf8mb4_unicode_ci`) |
| میزبانی | هاست اشتراکی cPanel — بدون worker، ایمیل sync + SMTP خارجی |
| آنالیتیکس | Matomo سلف‌هاست، بدون کوکی |

> **چرا Laravel 12 و نه ۱۱؟** سند PRD در ابتدا Laravel 11 را قفل کرده بود، اما
> Laravel 11 از پنجره‌ی پشتیبانی امنیتی خارج شده و سه advisory باز دارد که در هیچ
> نسخه‌ی ۱۱.x اصلاح نشده‌اند — از جمله CRLF injection در قانون اعتبارسنجی `email`
> که مستقیماً روی فرم تماس می‌نشیند. این با استاندارد امنیتی خودِ سند (بخش ۷-هـ)
> در تضاد بود. تصمیم در **D18** ثبت شده است؛ اسکلت و APIها عملاً یکسان‌اند و
> Filament 3 روی هر دو کار می‌کند.

---

## راه‌اندازی محلی

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# برای توسعه‌ی محلی می‌توانید SQLite استفاده کنید:
#   DB_CONNECTION=sqlite
#   DB_DATABASE="/absolute/path/to/database/database.sqlite"
touch database/database.sqlite

php artisan migrate --seed
php artisan storage:link

npm run build     # یا: npm run dev
php artisan serve
```

مقادیر `ADMIN_EMAIL` و `ADMIN_PASSWORD` را در `.env` تنظیم کنید؛ seeder کاربر مدیر
را از همین‌جا می‌سازد.

> `ADMIN_EMAIL` هم‌زمان **فهرست مجازِ ورود به `/admin`** است. خالی بودنش یعنی
> هیچ‌کس به پنل دسترسی ندارد — شکستِ امن.

---

## ساختار

```
app/
  Enums/              PlatformStatus · PostStatus · ContactPurpose · SkillCategory
  Filament/           منابع، صفحه‌ی تنظیمات و ویجت آمادگی محتوا
  Http/               کنترلرهای نازک + Form Request + میان‌افزار هدرهای امنیتی
  Models/             موجودیت‌ها با scopeهای active/ordered/published/featured
  Observers/          ابطال کش هنگام ذخیره از پنل
  Services/           Settings · ContactService · SitemapService · StructuredData
                      ReadingTime · Markdown · ImageOptimizer · ContentReadiness
  Support/            Slugger (فارسی→لاتین) · CacheKeys · helpers فارسی
  View/Composers/     داده‌ی مشترک لِی‌اوت (هویت، لینک‌ها، فعال‌سازی وبلاگ)

resources/views/
  components/ui/      کتابخانه‌ی x-ui — نگاشت ۱:۱ با جدول ۸.۲ سند
  components/layouts/ لِی‌اوت اصلی (مش + هدر گلس + فوتر)
  pages/              خانه · ویترین · کیس‌استادی · رزومه · تماس · وبلاگ
  dev/kit.blade.php   صفحه‌ی حسابرسی بصری کیت (فقط محیط local)

build-tools/
  icons.txt           فهرست گلیف‌های Tabler که وارد اسپرایت می‌شوند
  build-icons.mjs     سازنده‌ی public/icons.svg
```

---

## دیزاین‌سیستم

منبع حقیقت بصری: **UI Kit «گلس روشن»** (`docs/ui-kit-personal-site.html`).

- توکن‌های معنایی در `tailwind.config.js` و `resources/css/app.css`.
- **هیچ مقدار رنگ/سایه/تایپِ خامی بیرون از این دو فایل مجاز نیست.**
- دارک‌مود = فقط ۸ متغیر CSS؛ هیچ کامپوننتی دوباره نوشته نمی‌شود.
- کلاس‌های کامپوننتی باید در Blade به‌صورت **رشته‌ی کامل** نوشته شوند
  (`'btn-primary'`)، نه با درون‌یابی (`"btn-{$variant}"`) — اسکنر Tailwind فقط
  رشته‌های کامل را می‌بیند و بقیه را purge می‌کند.

برای مقایسه‌ی بصری، در محیط محلی به `/dev/kit` بروید.

### افزودن آیکون جدید

```bash
echo "نام-آیکون" >> build-tools/icons.txt   # نام از مجموعه‌ی Tabler outline
npm run icons
```

---

## فرمان‌های روزمره

```bash
npm run build          # ساخت CSS/JS + بازتولید اسپرایت آیکون
npm run icons          # فقط اسپرایت
./vendor/bin/pest      # کل مجموعه‌ی تست
php artisan optimize:clear
```

---

## مستندات

- [`docs/deployment-runbook.md`](docs/deployment-runbook.md) — استقرار روی cPanel، بکاپ و طرح بازگشت
- [`docs/decision-log.md`](docs/decision-log.md) — گزارش تصمیم‌ها (ADR-lite)
- [`docs/qa-checklist.md`](docs/qa-checklist.md) — چک‌لیست QA پیش از انتشار
- [`docs/content-guide.md`](docs/content-guide.md) — راهنمای ورود محتوا از پنل
