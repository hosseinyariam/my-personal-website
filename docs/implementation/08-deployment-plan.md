# برنامه‌ی استقرار — hosseinyariam.ir

**پروژه:** وب‌سایت شخصی حسین یاریم · **تاریخ:** ۲۰۲۶-۱۰-۲۶ · **زبان:** فارسی

این سند برنامه‌ی کامل استقرار روی هاست اشتراکی cPanel است. تا زمانی که
مالک سایت تأیید صریح نداده باشد، **هیچ‌کدام از مراحل تغییردهنده اجرا نشده است**.

---

## ۱. وضعیت فعلی هاست (اندازه‌گیری‌شده، نه فرض‌شده)

| مورد | مقدار واقعی | منبع |
|---|---|---|
| دامنه‌ی اصلی | `hosseinyariam.ir` | `DomainInfo::list_domains` |
| Document Root | `/home/hosseiny/public_html` | `DomainInfo::domains_data` |
| وضعیت `public_html` | **خالی** — سایت هرگز دیپلوی نشده | `Fileman::list_files` |
| نسخه PHP دامنه | `ea-php81` | `LangPHP::php_get_vhost_versions` |
| PHPهای قابل نصب | `ea-php82`، `ea-php83`، `ea-php84` | `LangPHP::php_get_installed_versions` |
| دیتابیس | **هیچ** دیتابیسی وجود ندارد | `Mysql::list_databases` |
| مصرف فضا | ۰٫۴۵ از ۵۱۲۰ مگابایت (۳۳۲ inode) | `Quota::get_quota_info` |
| SSL | Let's Encrypt معتبر تا ۲۳ ژانویه ۲۰۲۷ | بررسی مستقیم TLS |
| SSH | **بسته** — پورت ۲۲ پاسخ نمی‌دهد | آزمون TCP |
| کاربر هاست | `hosseiny` | `DomainInfo::domains_data` |
| IP | `88.135.68.1` | DNS |

**نتیجه‌ی مهم:** چون SSH بسته است، استقرار از طریق cPanel API (UAPI)
و `composer` روی خود هاست انجام می‌شود، نه از طریق git pull.

## ۲. مانع بحرانی و کاهش‌دهنده

**مانع:** دامنه روی PHP 8.1 است و Laravel 12 حداقل PHP 8.2 می‌خواهد.

**کاهش‌دهنده (تأییدشده):** نسخه‌های `ea-php82`/`ea-php83`/`ea-php84` روی هاست
نصب‌اند. نسخه‌ی PHP دامنه باید **پیش از** هر آپلودی به `ea-php83` سوئیچ شود.

**چرا 8.3 و نه 8.4:** محیط توسعه‌ی محلی PHP 8.3.29 است. هم‌نسخه‌کردن
تولید با توسعه، اختلاف رفتار غیرمنتظره را حذف می‌کند — این از فرض «روی
هاست هم درست کار می‌کند» مطمئن‌تر است.

**روش سوئیچ:** از پنل cPanel → MultiPHP Manager → انتخاب دامنه → `ea-php83`،
یا از API:
```bash
~/.cpanel/cpanel-api.sh LangPHP php_set_vhost_versions \
  vhost=hosseinyariam.ir version=ea-php83
```

## ۳. ترتیب مسیرها روی هاست (الگوی امن هاست اشتراکی)

```
/home/hosseiny/
├── hosseinyariam/          ← کل پروژه (کد، vendor، .env، storage)
│   ├── app/ bootstrap/ config/ database/ public/ …
│   └── .env                ← بیرون از docroot، الزام امنیتی
└── public_html/            ← فقط محتوای پوشه‌ی public/
    ├── index.php           ← مسیرها به ../hosseinyariam اصلاح می‌شود
    ├── .htaccess
    ├── build/ fonts/ icons.svg
    └── storage → symlink به ../hosseinyariam/storage/app/public
```

**چرا این چیدمان:** `.env`، `vendor` و `storage` هرگز نباید از وب قابل
دسترسی باشند. اگر کد داخل `public_html` باشد، یک اشتباه در `.htaccess`
می‌تواند فایل محیطی را لو بدهد. با این چیدمان، docroot فقط همان چیزی
است که باید عمومی باشد.

## ۴. مراحل استقرار (به ترتیب اجرا)

### مرحله ۰ — پیش‌نیاز: بکاپ و سوئیچ PHP

```bash
# ۱. بکاپ کامل پیش از هر تغییری (الزام §۱۵ دستورالعمل)
~/.cpanel/cpanel-api.sh Backup fullbackup_to_homedir

# ۲. سوئیچ PHP دامنه به 8.3
~/.cpanel/cpanel-api.sh LangPHP php_set_vhost_versions \
  vhost=hosseinyariam.ir version=ea-php83

# ۳. ساخت دیتابیس و کاربر
~/.cpanel/cpanel-api.sh Mysql create_database name=hosseinyarim_ir
~/.cpanel/cpanel-api.sh Mysql create_user user=hosseinyarim_db
~/.cpanel/cpanel-api.sh Mysql set_privileges_on_database \
  user=hosseinyarim_db database=hosseinyarim_ir privileges=ALL
```

**بدون بکاپ تأییدشده، استقرار شروع نمی‌شود.**

### مرحله ۱ — ساخت بسته‌ی انتشار روی سیستم محلی

```bash
cd /home/hossein/Projects/hosseinyariam

# وابستگی‌های تولید — بدون پکیج‌های توسعه
composer install --no-dev --optimize-autoloader

# دارایی‌های فرانت‌اند
npm ci && npm run build

# اطمینان از سالم بودن
php artisan test
php artisan site:health
```

خروجی مورد انتظار: `public/build/`، `public/fonts/` ساخته شده و
`site:health` بدون مشکل بحرانی.

### مرحله ۲ — آپلود

پروژه **بدون** `node_modules`، `.git` و `database/database.sqlite`
بسته‌بندی و آپلود می‌شود. روش: از cPanel File Manager (آپلود zip و
استخراج) یا از API:

```bash
# بسته‌بندی محلی
tar czf /tmp/hosseinyariam-deploy.tar.gz \
  --exclude=node_modules --exclude=.git --exclude='database/*.sqlite' \
  --exclude=tests --exclude=.env -C /home/hossein/Projects/hosseinyariam .
```

سپس آپلود و استخراج در `/home/hosseiny/hosseinyariam/`.
محتوای `public/` به `public_html/` منتقل می‌شود.

### مرحله ۳ — اصلاح مسیرها در `index.php` داخل docroot

```php
// public_html/index.php
require __DIR__.'/../hosseinyariam/vendor/autoload.php';
$app = require_once __DIR__.'/../hosseinyariam/bootstrap/app.php';
```

> اگر cPanel اجازه‌ی تغییر docroot دامنه بدهد، به‌جای این جابه‌جایی،
> docroot مستقیماً روی `~/hosseinyariam/public` تنظیم شود — ساده‌تر و
> کم‌خطاتر است.

### مرحله ۴ — پیکربندی محیط روی هاست

`~/hosseinyariam/.env` ساخته می‌شود (فایل جدا، **نه** کپی از `.env` محلی):

```dotenv
APP_NAME="حسین یاریم"
APP_ENV=production
APP_KEY=                    # با key:generate ساخته می‌شود
APP_DEBUG=false             # الزام امنیتی قطعی
APP_TIMEZONE=Asia/Tehran
APP_URL=https://hosseinyariam.ir

APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hosseinyarim_ir
DB_USERNAME=hosseinyarim_db
DB_PASSWORD=              # فقط روی هاست، نه در مخزن

SESSION_DRIVER=database
SESSION_ENCRYPT=true      # الزام امنیتی تولید
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=sync
CACHE_STORE=database
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_SCHEME=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="hello@hosseinyariam.ir"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="حسین یاریم"
ADMIN_EMAIL=               # فهرست مجاز ورود به پنل
ADMIN_PASSWORD=            # فقط اینجا، نه در کد

CONTACT_NOTIFY_EMAIL=
AI_ENABLED=false
```

**نکات امنیتی این مرحله:**
- `APP_DEBUG=false` — بدون آن، هر خطا stack trace و متغیرهای محیطی را لو می‌دهد.
- `SESSION_ENCRYPT=true` — روی هاست اشتراکی، رمزنگاری سشن لایه‌ی دفاعی لازم است.
- `ADMIN_EMAIL` باید پر باشد، وگرنه هیچ‌کس نمی‌تواند وارد پنل شود
  (رفتار عمدی «شکست امن» — در `User::canAccessPanel` پیاده شده).
- `.env` با مجوز `600` و بیرون از docroot.

### مرحله ۵ — اجرای migration و seed

```bash
cd ~/hosseinyariam
php artisan key:generate
php artisan migrate --force        # --force لازم است چون APP_ENV=production
php artisan db:seed --force        # ProductionSeeder — بدون محتوای ساختگی
php artisan storage:link
```

**`ProductionSeeder` چه می‌کند:**
- ۱۲ ماژول، ۹ بخش صفحه اصلی، ۷ تنظیم عمومی، ۷ دسته‌ی یادداشت را seed می‌کند.
- کاربر مدیر را **فقط** از `ADMIN_EMAIL` و `ADMIN_PASSWORD` می‌سازد.
- اگر این دو خالی باشند، کاربر ساخته نمی‌شود و هشدار می‌دهد — رمز جعلی نمی‌سازد.
- **هیچ محتوای نمونه نمی‌سازد.** (محتوای نمایشی فقط در `DemoSeeder` است
  که در تولید مسدود می‌شود.)

**ممنوعیت قطعی:** `migrate:fresh`، `db:wipe` و مشابه‌شان هرگز روی این
محیط اجرا نمی‌شوند (الزام §۱۵).

### مرحله ۶ — بهینه‌سازی و cron

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**cron** (از cPanel → Cron Jobs، یک ورودی):

```cron
* * * * * /usr/local/bin/php /home/hosseiny/hosseinyariam/artisan schedule:run >> /home/hosseiny/logs/scheduler.log 2>&1
```

زمان‌بند این کارها را اجرا می‌کند:
| کار | زمان | نقش |
|---|---|---|
| `contents:publish-scheduled` | هر دقیقه | انتشار محتوای زمان‌بندی‌شده |
| `contact:prune-spam` | روزانه ۳:۳۰ | پاک‌سازی اسپم قدیمی |
| `cache:prune-stale` | ساعتی | پاک‌سازی کش منقضی |

### مرحله ۷ — بررسی نهایی

```bash
php artisan site:health
```

باید همه‌ی بخش‌ها «سالم» باشند. اگر «کاربر مدیر» بحرانی بود، یعنی
`ADMIN_EMAIL` خالی است یا کاربرش ساخته نشده.

سپس بررسی بیرونی:
```bash
curl -sI https://hosseinyariam.ir/ | head -5      # انتظار: 200
curl -s  https://hosseinyariam.ir/robots.txt      # انتظار: Disallow /admin
curl -sI https://hosseinyariam.ir/admin           # انتظار: 302 به login
```

## ۵. برنامه‌ی بازگشت (Rollback)

| سناریو | اقدام |
|---|---|
| سایت بالا نمی‌آید | `php artisan down`، بررسی `storage/logs/laravel.log`، سپس rollback |
| migration مشکل‌دار | `php artisan migrate:rollback --step=1` (هر migration `down()` سالم دارد) |
| کد مشکل‌دار | استخراج بکاپ tar مرحله ۰ روی `~/hosseinyariam` |
| دیتابیس خراب | بازگرداندن دامپ از `~/backups/` |
| سوئیچ PHP اشتباه | برگرداندن به نسخه‌ی قبلی با `php_set_vhost_versions` |

**RPO:** یک هفته (بکاپ هفتگی) · **RTO:** کمتر از ۲۴ ساعت با همین سند.

## ۶. چک‌لیست پیش از اعلام انتشار

- [ ] بکاپ کامل گرفته و تأیید شده
- [ ] PHP دامنه روی `ea-php83`
- [ ] دیتابیس و کاربر ساخته شده
- [ ] `.env` روی هاست با `APP_DEBUG=false` و `APP_ENV=production`
- [ ] `.env` بیرون docroot با مجوز ۶۰۰
- [ ] `APP_KEY` ساخته شده
- [ ] migrationها اجرا شده (بدون `fresh`/`wipe`)
- [ ] `ProductionSeeder` اجرا شده و هیچ محتوای ساختگی نیست
- [ ] `ADMIN_EMAIL` پر و کاربر مدیر فعال
- [ ] `site:health` بدون مشکل بحرانی
- [ ] SSL فعال و HTTP→HTTPS ریدایرکت
- [ ] cron با `schedule:run` فعال
- [ ] کش‌ها ساخته شده
- [ ] `storage:link` ساخته شده
- [ ] بررسی بیرونی: سایت ۲۰۰ می‌دهد، `/admin` نیازمند ورود است
- [ ] `robots.txt` پنل را مسدود می‌کند
- [ ] `sitemap.xml` معتبر است

## ۷. مواردی که نیازمند تأیید صریح مالک است

این موارد **اجرا نشده‌اند** و منتظر تأیید هستند:

1. **سوئیچ نسخه‌ی PHP دامنه** — تغییر تنظیمات محیط تولید.
2. **ساخت دیتابیس روی هاست** — تغییر ماندگار.
3. **آپلود کد روی هاست** — اولین استقرار واقعی.
4. **اجرای migration و seed** — تغییر شِما در محیط تولید.
5. **فعال‌سازی cron** — شروع اجرای خودکار.
6. **ورود اعتبارنامه‌های واقعی** (رمز دیتابیس، SMTP، `ADMIN_PASSWORD`) —
   این‌ها باید توسط مالک در `.env` هاست وارد شوند، نه توسط من.
7. **ورود محتوای واقعی** — پروفایل، پروژه‌ها، محصولات. سایت با
   `ProductionSeeder` خالی بالا می‌آید و صفحه‌ی راه‌اندازی نشان می‌دهد
   تا محتوای واقعی وارد شود.
