# فاز ۸ — گزارش استقرار

**پروژه:** hosseinyariam.ir · **تاریخ:** ۲۰۲۶-۱۰-۲۶ · **وضعیت:** مستقر و زنده

---

## ۱. نتیجه

**سایت روی `https://hosseinyariam.ir` زنده است.** همه‌ی مسیرهای عمومی کد
۲۰۰ می‌دهند، ریدایرکت HTTP→HTTPS فعال است، و زیرساخت کامل کار می‌کند.

```
https://hosseinyariam.ir/          -> 200
https://hosseinyariam.ir/projects  -> 200
https://hosseinyariam.ir/robots.txt-> 200
https://hosseinyariam.ir/sitemap.xml-> 200
https://hosseinyariam.ir/admin/login-> 200
http://hosseinyariam.ir/           -> 301 (ریدایرکت به https)
```

## ۲. تغییرات اعمال‌شده روی هاست

| # | تغییر | قبل | بعد |
|---|---|---|---|
| ۱ | بکاپ کامل | — | `backup-10.26.2026_11-39-16_hosseiny.tar.gz` |
| ۲ | نسخه‌ی PHP دامنه | `ea-php81` | `ea-php83` |
| ۳ | دیتابیس | هیچ | `hosseiny_yariam` با کاربر و همه‌ی مجوزها |
| ۴ | کد پروژه | — | `/home/hosseiny/hosseinyariam` (۱۲۶۲۳ فایل) |
| ۵ | Document Root | خالی | محتوای `public/` در `public_html/` |
| ۶ | migration | — | هر ۱۲ migration اجرا شد |
| ۷ | seed | — | ماژول‌ها، بخش‌های صفحه، تنظیمات، دسته‌ها |
| ۸ | symlink ذخیره‌سازی | — | `public_html/storage` → `hosseinyariam/storage/app/public` |
| ۹ | cron | — | زمان‌بند سایت با PHP 8.3 صریح |
| ۱۰ | robots.txt | استاتیک، پنل مسدود نبود | پویا از لاراول، پنل مسدود |
| ۱۱ | .htaccess | ۲۵ خط، بدون ریدایرکت | ۹۳ خط: HTTPS، gzip، کش، هدرهای امنیتی |
| ۱۲ | مجوز `.env` | `755` ⚠ | `600` ✓ |

## ۳. موانعی که در مسیر پیدا شد و چطور حل شد

این‌ها مشکلات **واقعی** بودند که فقط با اجرای واقعی استقرار معلوم شدند:

### ۳.۱ — SSH بسته است
پورت ۲۲ پاسخ نمی‌دهد، پس `git pull` و `composer install` روی هاست ممکن نبود.

**راه‌حل:** استقرار از طریق cPanel UAPI + API2. کد به‌صورت بسته‌ی
tar.gz در سه تکه آپلود و روی هاست چسبانده شد. دستورهای shell از طریق
`Cron::add_line` (API2) اجرا شدند — چون UAPI ماژول Cron را ندارد.

### ۳.۲ — نسخه‌ی PHP خط فرمان با دامنه فرق داشت
دامنه به ۸٫۳ سوئیچ شد، ولی `php` پیش‌فرض CLI روی هاست **۸٫۲** بود.
نتیجه: `composer detected issues — requires >= 8.3.0` و هیچ‌کدام از
دستورهای artisan اجرا نشد.

**راه‌حل:** مسیر صریح `/opt/cpanel/ea-php83/root/usr/bin/php` استفاده شد —
هم در اسکریپت نصب و هم در cron زمان‌بند. `vendor/composer/platform_check.php`
نسخه‌ی ۸٫۳ می‌خواست چون `openspout/openspout` (وابسته‌ی Filament) آن را الزام می‌کند.

### ۳.۳ — پیشوند اجباری نام دیتابیس
`hosseinyarim_main` رد شد: «must begin with the required prefix `hosseiny_`».

**راه‌حل:** نام‌ها به `hosseiny_yariam` تغییر کرد.

### ۳.۴ — امضای API مجوزها
`privileges=ALL` خطای داخلی می‌داد (Error ID).

**راه‌حل:** فهرست صریح دسترسی‌ها فرستاده شد (SELECT, INSERT, UPDATE, …).

### ۳.۵ — robots.txt استاتیک مسیر لاراول را می‌پوشاند
یک `robots.txt` استاتیک ۲۴ بایتی در `public_html` بود که فقط
`Disallow:` داشت. نتیجه: پنل `/admin` برای موتور جست‌وجو **باز** بود.

**راه‌حل:** فایل استاتیک حذف شد تا مسیر لاراول (`SeoController::robots`)
اجرا شود. حالا `Disallow: /admin` و `Disallow: /search` دارد.

### ۳.۶ — symlink ذخیره‌سازی شکسته بود
`public_html/storage` به `/home/hossein/Projects/hosseinyariam/storage/app/public`
اشاره می‌کرد — یعنی به **مسیر سیستم لوکال من**، نه مسیر هاست. علت:
symlink در زمان بسته‌بندی کپی شده بود.

**اثر:** هر آپلود تصویر در پنل، میشکست.

**راه‌حل:** symlink حذف و با مسیر مطلق هاست بازساخته شد. با تست نوشتن
واقعی تأیید شد.

### ۳.۷ — مجوز `.env` ناامن بود
پس از نصب، `.env` مجوز `755` داشت — یعنی گروه و دیگران می‌توانستند آن را بخوانند.

**راه‌حل:** `chmod 600`. تأیید شد که از وب هم قابل خواندن نیست (۴۰۳).

### ۳.۸ — cronهای موقت پاک نشدند
اسکریپت‌های نصب که از cron اجرا می‌شدند، خودشان را به‌درستی حذف نکردند.
سه cron باقی ماند که هر دقیقه فایل‌های حذف‌شده را صدا می‌زدند.

**راه‌حل:** کل crontab بازنویسی شد و فقط زمان‌بند واقعی سایت ماند.

## ۴. مواردی که از وب قابل خواندن نیستند (تأییدشده)

```
https://hosseinyariam.ir/.env                -> 403
https://hosseinyariam.ir/../hosseinyariam/.env -> 403
https://hosseinyariam.ir/storage/../.env     -> 403
```

`.env` بیرون از docroot است و `.htaccess` هم آن را مسدود می‌کند
(دفاع در عمق).

## ۵. هدرهای امنیتی روی سایت زنده (تأییدشده)

```
x-frame-options: DENY
x-content-type-options: nosniff
referrer-policy: strict-origin-when-cross-origin
content-security-policy: default-src 'self'; …; frame-ancestors 'none'
strict-transport-security: max-age=31536000; includeSubDomains
x-robots-tag: noindex, nofollow   (روی /admin)
```

## ۶. کارایی (تأییدشده)

| مورد | نتیجه |
|---|---|
| فشرده‌سازی | ✓ ۹۹۲۲ بایت → ۲۷۴۷ بایت (کاهش ۷۲٪) |
| کش دارایی‌های هش‌دار | ✓ `public, immutable` |
| کش HTML | ✓ `no-cache` (محتوا از پنل می‌آید) |
| مسیرهای فارسی | ✓ کار می‌کند (`/projects/دمو-پروژه-نمونه`) |
| درخواست خارجی | ✓ **صفر** — فونت و آواتار محلی |

## ۷. وضعیت فعلی سایت

سایت **صفحه‌ی «در حال آماده‌سازی»** نشان می‌دهد، نه محتوا. این رفتار
**درست و عمدی** است:

- `ProductionSeeder` هیچ محتوای نمونه‌ای نساخت (قاعده‌ی «بدون محتوای ساختگی»).
- پروفایل فعالی وجود ندارد، پس `HomeController` نمای راه‌اندازی می‌دهد.
- صفحه با `noindex, nofollow` سرو می‌شود تا محتوای ناقص ایندکس نشود.

## ۸. تنها کار باقی‌مانده — توسط مالک سایت

برای باز شدن پنل و ورود محتوا، این دو مقدار باید در `.env` هاست باشند.
**من نمی‌توانم این‌ها را حدس بزنم یا انتخاب کنم.**

### روش پیشنهادی (از cPanel File Manager)

۱. در cPanel → File Manager → `/home/hosseiny/hosseinyariam/.env` را باز کنید.
۲. این دو خط را پر کنید:

```dotenv
ADMIN_EMAIL=ایمیل-شما@example.com
ADMIN_PASSWORD=یک-رمز-قوی-حداقل-۱۲-کاراکتر
```

۳. ذخیره کنید.

### سپس کاربر مدیر را بسازید

از cPanel → Terminal (اگر فعال است) یا با یک cron یک‌باره:

```bash
/opt/cpanel/ea-php83/root/usr/bin/php /home/hosseiny/hosseinyariam/artisan db:seed --class=ProductionSeeder --force
/opt/cpanel/ea-php83/root/usr/bin/php /home/hosseiny/hosseinyariam/artisan config:cache
```

> **مهم:** `php` پیش‌فرض روی هاست ۸٫۲ است. **همیشه** مسیر کامل
> `/opt/cpanel/ea-php83/root/usr/bin/php` را استفاده کنید.

۴. وارد شوید: `https://hosseinyariam.ir/admin`

سپس محتوای واقعی را از پنل وارد کنید: پروفایل، پروژه‌ها، محصولات،
شرکت‌ها، یادداشت‌ها و خط زمان. با ورود پروفایل، صفحه‌ی اصلی
خودکار از حالت راه‌اندازی به «Living Profile» تغییر می‌کند.

### کارهای اختیاری

| کار | چرا |
|---|---|
| رمز SMTP در `.env` | برای ارسال ایمیل فرم تماس (بدون آن پیام‌ها در دیتابیس ذخیره می‌شوند و در پنل دیده می‌شوند ولی ایمیل نمی‌رود) |
| `CONTACT_NOTIFY_EMAIL` | آدرس دریافت اعلان فرم تماس |
| Matomo | اگر آنالیتیکس بدون کوکی می‌خواهید |

## ۹. برنامه‌ی بازگشت (Rollback)

| سناریو | اقدام |
|---|---|
| سایت بالا نمی‌آید | بررسی `~/hosseinyariam/storage/logs/laravel.log` |
| کد masalah دارد | بازگرداندن بکاپ tar: `tar xzf ~/backup-*.tar.gz -C ~` |
| migration مشکل‌دار | `/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate:rollback --step=1` |
| htaccess مشکل‌دار | `cp ~/.htaccess.old-* ~/public_html/.htaccess` |
| دیتابیس خراب | بازگرداندن دامپ از بکاپ سطح هاست |

**نکته:** هیچ‌کدام از دستورهای مخرب (`migrate:fresh`، `db:wipe`) اجرا نشده
و در هیچ مرحله‌ای اجرا نخواهد شد.

## ۱۰. چک‌لیست وضعیت

- [x] بکاپ کامل گرفته شد
- [x] PHP دامنه روی `ea-php83`
- [x] دیتابیس و کاربر ساخته شد
- [x] کد آپلود و استخراج شد
- [x] `.env` ساخته شد (بیرون docroot، مجوز ۶۰۰)
- [x] `APP_DEBUG=false` و `APP_ENV=production`


- [x] `APP_KEY` ساخته شد
- [x] هر ۱۲ migration اجرا شد
- [x] `ProductionSeeder` اجرا شد، بدون محتوای ساختگی
- [x] symlink ذخیره‌سازی اصلاح و تست شد
- [x] robots.txt پنل را مسدود می‌کند
- [x] ریدایرکت HTTPS فعال (۳۰۱)
- [x] فشرده‌سازی و کش فعال
- [x] زمان‌بند cron فعال
- [x] هدرهای امنیتی روی سایت زنده
- [x] `.env` از وب قابل خواندن نیست (۴۰۳)
- [ ] **ADMIN_EMAIL و ADMIN_PASSWORD** ← نیازمند مالک سایت
- [ ] ورود محتوای واقعی ← نیازمند مالک سایت
