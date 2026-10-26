# hosseinyariam.ir

وب‌سایت شخصی و زیرساخت هویت حرفه‌ای — پیاده‌سازی ۱۱ سند استراتژی، معماری و طراحی.

> **اصل حاکم:** محتوا از پنل می‌آید، نه از کد. برای تغییر هر متن، تصویر یا
> افزودن پروژه‌ی جدید نیازی به استقرار مجدد نیست.

---

## ماهیت محصول

این سایت یک **Professional Identity Hub** است، نه رزومه، نه Portfolio،
نه وبلاگ و نه سایت یک محصول. هدف: وقتی کسی نام «حسین یاریم» را جست‌وجو
می‌کند، بفهمد او کیست، چه ساخته و چگونه به مسائل نگاه می‌کند.

```
مسائل واقعی را به راهکارهای فناورانه و محصولات کاربردی تبدیل می‌کنم.
```

## استک

| لایه | فناوری |
|---|---|
| بک‌اند | PHP 8.3 · Laravel 12.70 |
| پنل مدیریت | Filament 3.3 (۲۳ منبع) |
| فرانت‌اند | Blade · Tailwind CSS 4 · جاوااسکریپت وانیلا (بدون SPA) |
| Asset | Vite 7 |
| دیتابیس | MySQL 8 / MariaDB 10.6+ · `utf8mb4_unicode_ci` |
| تست | Pest 3.8 · ۹۰ تست |
| میزبانی | هاست اشتراکی cPanel — بدون worker، صف sync، کش database |
| فونت | وزیرمتن متغیر، **خودمیزبان** |

**بدون** React، Vue، Next.js، Docker، Redis، Elasticsearch — طبق منع صریح مستندات.

## راه‌اندازی محلی

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# دیتابیس محلی SQLite کافی است
php artisan migrate --seed     # در محیط local، DemoSeeder هم اجرا می‌شود

npm run build                  # یا: npm run dev
php artisan serve
```

سپس `http://localhost:8000`.

**برای ورود به پنل**، در `.env` این‌ها را پر کنید:

```dotenv
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=یک-رمز-قوی-حداقل-۱۲-کاراکتر
```

سپس:

```bash
php artisan db:seed --force      # کاربر مدیر از همین دو متغیر ساخته می‌شود
```

`ADMIN_EMAIL` هم سازنده‌ی کاربر است و هم **فهرست مجاز ورود به `/admin`**.
اگر خالی باشد، هیچ‌کس نمی‌تواند وارد پنل شود — این رفتار عمدی «شکست امن»
است، نه باگ.

## ساختار مسیرها

### عمومی

| مسیر | نقش |
|---|---|
| `/` | صفحه اصلی — «Living Profile» |
| `/biography` | پرونده‌ی حرفه‌ای (نه رزومه) |
| `/projects` · `/projects/{slug}` | فهرست و پرونده‌ی پروژه |
| `/products` · `/products/{slug}` | فهرست و جزئیات محصول |
| `/companies` · `/companies/{slug}` | شرکت‌ها |
| `/notes` · `/notes/{slug}` | یادداشت‌ها و مقالات |
| `/journey` | خط زمان حرفه‌ای (Curated) |
| `/contact` | کانال‌های ارتباط + فرم امن |
| `/search` | جست‌وجوی داخلی (`noindex`) |
| `/feed` · `/sitemap.xml` · `/robots.txt` | خروجی‌های SEO |

هر مسیر متعلق به یک **ماژول** است و با middleware `module:` محافظت
می‌شود. ماژول خاموش = ۴۰۴ صریح. خاموش‌کردن ماژول **داده را حذف نمی‌کند**.

### پنل مدیریت `/admin`

گروه‌بندی منطبق بر CMS Blueprint §3: هویت · نمونه‌کار · محتوا · مسیر ·
نمایش · رسانه · تنظیمات.

## تصمیم‌های کلیدی معماری

این‌ها جاهایی هستند که مستندات متناقض بودند و تصمیم مستند گرفته شد
(۱۷ تصمیم در `docs/implementation/01-discovery-and-decisions.md`):

| موضوع | تصمیم |
|---|---|
| وضعیت انتشار و وضعیت دامنه | دو مفهوم جدا. `Note`/`Company` وضعیت انتشار دارند؛ `Project`/`Product` وضعیت دامنه دارند و انتشارشان با `published_at` + `visibility` سنجیده می‌شود |
| اسلاگ فارسی | **حفظ حروف فارسی**، نه نویسه‌گردانی. `Str::slug` متن فارسی را به ناخوانا تبدیل می‌کند که برای SEO بی‌ارزش است |
| انتشار | **fail-closed**: محتوای بدون `published_at` منتشرشده نیست، حتی اگر وضعیتش `published` باشد |
| جدایی موجودیت‌ها | `Project ≠ Product ≠ Company` — رابطه‌شان چند‌به‌چند است و حذف یکی، دیگری را پاک نمی‌کند |
| یکپارچگی چندریختی | Observer ردیف‌های `seo_metadata` و `media_attachments` یتیم را پاک می‌کند |
| وابستگی خارجی | صفر. فونت خودمیزبان، آواتار محلی، بدون CDN |

## تست

```bash
php artisan test            # یا: vendor/bin/pest
php vendor/bin/pint --test  # سبک کد
```

| فایل | پوشش |
|---|---|
| `Feature/PublicPagesTest.php` | رندر واقعی هر صفحه با بررسی محتوا (نه فقط status) |
| `Feature/SecurityAndIntegrityTest.php` | کنترل دسترسی، SSRF، یکپارچگی داده، اسلاگ |
| `Feature/ConsoleCommandsTest.php` | فرمان‌های زمان‌بند و سلامت |

> **فلسفه‌ی تست:** صفحه‌ی خالی هم ۲۰۰ برمی‌گرداند. تست‌ها محتوای
> رندرشده را بررسی می‌کنند، نه فقط کد وضعیت.

## فرمان‌های مفید

```bash
php artisan site:health                 # smoke test — پیش و پس از استقرار
php artisan site:health --json          # خروجی JSON برای پایش
php artisan contents:publish-scheduled  # انتشار محتوای زمان‌بندی‌شده
php artisan contact:prune-spam          # پاک‌سازی اسپم قدیمی
php artisan admin:create-user           # ساخت کاربر مدیر با رمز امن
```

## مستندات

### اسناد مرجع پروژه (`docs/`)

یازده سند استراتژی، معماری، دیتابیس و طراحی که پیاده‌سازی از آن‌ها استخراج شده.

### اسناد تصمیم و اجرا (`docs/implementation/`)

| سند | محتوا |
|---|---|
| [`01-discovery-and-decisions.md`](docs/implementation/01-discovery-and-decisions.md) | ممیزی محیط، ۱۷ تصمیم ثبت‌شده، حل تناقض‌های مستندات |
| [`02-technical-baseline.md`](docs/implementation/02-technical-baseline.md) | Baseline قفل‌شده: مدل دامنه، جدول مسیرها، سیاست امنیت |
| [`07-qa-report.md`](docs/implementation/07-qa-report.md) | گزارش QA، ۱۶ باگ اصلاح‌شده، ممیزی امنیت/SEO/دسترس‌پذیری |
| [`08-deployment-plan.md`](docs/implementation/08-deployment-plan.md) | برنامه‌ی کامل استقرار روی cPanel + Rollback |

## اصول غیرقابل‌مذاکره

1. **Evidence Over Claims** — هیچ مهارت، دستاورد یا آماری بدون شاهد واقعی منتشر نمی‌شود.
2. **بدون محتوای ساختگی** — محتوای نمایشی فقط در `DemoSeeder` است و در تولید مسدود می‌شود.
3. **Identity First** — صفحه اصلی در ۳۰ ثانیه به «من کیستم / چه ساخته‌ام / الان روی چه کار می‌کنم» پاسخ می‌دهد.
4. **شکست امن** — نبود پیکربندی، دسترسی را باز نمی‌کند؛ می‌بندد.
5. **RTL واقعی** — ویژگی‌های منطقی CSS، نه `text-align: right`.
6. **محتوا از پنل** — تغییر محتوا نیازمند دیپلوی نیست.
7. **صفر وابستگی خارجی** — فونت، آواتار و دارایی‌ها محلی‌اند.
