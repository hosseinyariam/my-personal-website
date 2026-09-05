# Runbook استقرار — cPanel

مخاطب: تنها نگهدارنده‌ی پروژه. این سند «مکتوب» است نه شفاهی — الزام فاز ۶ سند PRD
و کاهش‌دهنده‌ی ریسک Bus Factor.

**RPO:** یک هفته (دامپ هفتگی + بکاپ سطح هاست) · **RTO:** کمتر از ۲۴ ساعت با همین سند.

---

## ۰. پیش‌نیازهای میزبان

- PHP ≥ 8.2 با افزونه‌های `intl`، `gd`، `mbstring`، `pdo_mysql`، `zip`، `fileinfo`، `openssl`
- MySQL 8 یا MariaDB 10.6+
- دسترسی به cron و AutoSSL
- حساب SMTP خارجی (پیش‌فرض Brevo — پلن رایگان کافی است)

---

## ۱. آماده‌سازی روی سیستم محلی

```bash
# با همان نسخه‌ی PHP میزبان
composer install --no-dev --optimize-autoloader
npm ci && npm run build
./vendor/bin/pest          # هیچ‌چیز شکسته نباشد
```

خروجی `public/build/`، `public/icons.svg` و `public/fonts/` باید ساخته شده باشند.

---

## ۲. بکاپ کامل پیش از هر تغییر

```bash
# روی سرور
mysqldump -u USER -p DATABASE > ~/backups/pre-deploy-$(date +%F).sql
tar czf ~/backups/pre-deploy-$(date +%F).tar.gz ~/hosseinyariam ~/public_html
```

بکاپ سطح cPanel هم جداگانه گرفته شود. **بدون بکاپ تأییدشده، استقرار شروع نمی‌شود.**

---

## ۳. آپلود و چیدمان مسیرها

الگوی امن روی هاست اشتراکی: **کد بیرون از docroot، فقط `public/` داخل آن.**

```
~/hosseinyariam/        ← کل پروژه (شامل .env، vendor، storage)
~/public_html/          ← محتوای پوشه‌ی public/
```

۱. کل پروژه را (بدون `node_modules` و `.git`) در `~/hosseinyariam/` آپلود کنید.
۲. محتوای `public/` را در `~/public_html/` بگذارید.
۳. در `~/public_html/index.php` مسیرها را اصلاح کنید:

```php
require __DIR__.'/../hosseinyariam/vendor/autoload.php';
$app = require_once __DIR__.'/../hosseinyariam/bootstrap/app.php';
```

> اگر cPanel اجازه‌ی تغییر docroot دامنه را می‌دهد، به‌جای این جابه‌جایی
> docroot را مستقیماً روی `~/hosseinyariam/public` تنظیم کنید — ساده‌تر و کم‌خطاتر است.

### مجوزها

```bash
chmod -R 755 ~/hosseinyariam
chmod -R 775 ~/hosseinyariam/storage ~/hosseinyariam/bootstrap/cache
chmod 600 ~/hosseinyariam/.env
```

`.env` باید **بیرون از docroot** بماند — این الزام NFR است.

---

## ۴. پیکربندی و دیتابیس

```bash
cd ~/hosseinyariam
cp .env.example .env    # سپس مقادیر واقعی را پر کنید
php artisan key:generate
```

حداقل مقادیر لازم:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hosseinyariam.ir
DB_DATABASE= / DB_USERNAME= / DB_PASSWORD=
MAIL_USERNAME= / MAIL_PASSWORD=
ADMIN_EMAIL=   ← هم سازنده‌ی کاربر مدیر، هم فهرست مجاز ورود به /admin
ADMIN_PASSWORD=
```

```bash
php artisan migrate --force
php artisan db:seed --force      # فقط در استقرار اول
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> **هرگز** مستقیم روی دیتابیس تغییر ندهید. هر تغییر شِما = یک migration جدید.

---

## ۵. SSL

AutoSSL را در cPanel برای `hosseinyariam.ir` و `www` فعال کنید و ریدایرکت
HTTP→HTTPS را روشن کنید. اپلیکیشن در محیط production خودش HTTPS را اجبار می‌کند.

---

## ۶. Cron

```cron
# بکاپ هفتگی دیتابیس — جمعه ساعت ۳ بامداد
0 3 * * 5 /usr/local/bin/php ~/hosseinyariam/artisan db:dump >> ~/logs/backup.log 2>&1

# آرشیو Matomo (اگر نصب است) — هر ساعت
5 * * * * /usr/local/bin/php ~/analytics/console core:archive --url=https://analytics.hosseinyariam.ir/ > /dev/null 2>&1
```

اگر `db:dump` تعریف نشده، معادل مستقیمش:

```cron
0 3 * * 5 mysqldump -u USER -pPASS DB | gzip > ~/backups/db-$(date +\%F).sql.gz
```

---

## ۷. چک‌لیست پس از استقرار

- [ ] `/` ، `/platforms` ، `/platforms/vinora` ، `/resume` ، `/contact` باز می‌شوند
- [ ] `/sitemap.xml` و `/robots.txt` خروجی درست می‌دهند
- [ ] یک نشانی بی‌معنی، صفحه‌ی ۴۰۴ سفارشی می‌دهد (نه خطای خام سرور)
- [ ] ورود به `/admin` کار می‌کند و مهمان ریدایرکت می‌شود
- [ ] یک پیام آزمایشی از فرم تماس ارسال شود → هم در پنل ثبت شود، هم ایمیل برسد
- [ ] `APP_DEBUG=false` است و هیچ stack trace‌ای عمومی دیده نمی‌شود
- [ ] دارک/لایت و RTL در موبایل درست است
- [ ] سه رویداد Matomo (`resume_download`، `contact_submit`، `demo_click`) ثبت می‌شوند
- [ ] ویجت «آمادگی محتوا» در داشبورد وضعیت واقعی را نشان می‌دهد

---

## ۸. طرح بازگشت (Rollback)

ریلیز قبلی را نگه دارید:

```bash
mv ~/hosseinyariam ~/hosseinyariam-$(date +%F)
# ریلیز قبلی را برگردانید، سپس:
mysql -u USER -p DATABASE < ~/backups/pre-deploy-YYYY-MM-DD.sql
php artisan optimize:clear
```

مهاجرت‌های برگشت‌ناپذیر (حذف ستون) پیش از اجرا باید در Decision Log ثبت شوند.

---

## ۹. تمرین بازیابی (یک‌بار، الزامی)

پیش از اعلام لانچ، یک‌بار بازیابی کامل را تمرین کنید و نتیجه را اینجا ثبت کنید:

| تاریخ تمرین | منبع بکاپ | مدت بازیابی | نتیجه |
|---|---|---|---|
| _تکمیل شود_ | | | |

بدون این ردیف، RTO فقط یک ادعاست.

---

## ۱۰. به‌روزرسانی بعدی (بدون تغییر محتوا)

```bash
# محلی
composer install --no-dev --optimize-autoloader && npm ci && npm run build && ./vendor/bin/pest
# سرور: بکاپ → آپلود فایل‌های تغییرکرده → سپس
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> **تغییر محتوا هرگز به استقرار نیاز ندارد** — از `/admin` انجام می‌شود.
