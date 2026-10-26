# فاز ۲ — Baseline فنی

**پروژه:** hosseinyariam.ir · **تاریخ:** ۲۰۲۶-۱۰-۲۶ · **وضعیت:** قفل‌شده برای پیاده‌سازی

این سند مرجع قطعی پیاده‌سازی است. هرجا با سند دیگری تعارض داشت، تصمیم‌های `D1`–`D15` در `01-discovery-and-decisions.md` حاکم است.

---

## ۱. استک قفل‌شده

| لایه | فناوری | نسخه واقعی نصب‌شده |
|---|---|---|
| زبان | PHP | 8.3.29 (محلی) · ۸.۳ روی هاست |
| فریم‌ورک | Laravel | 12 |
| پنل مدیریت | Filament | 3.3 |
| فرانت‌اند | Blade + Tailwind CSS | Tailwind 4 |
| Asset | Vite | 7 |
| تست | Pest | 3.8 |
| دیتابیس توسعه | SQLite | — |
| دیتابیس تولید | MySQL 8 / MariaDB 10.6+ | `utf8mb4_unicode_ci` |
| صف | sync | بدون worker |
| کش | `database` (سازگار هاست اشتراکی) | بدون Redis |
| جست‌وجو | دیتابیس | — |
| زمان | Asia/Tehran | — |
| زبان | فارسی، Locale-aware | — |

**بدون** React، Vue، Next.js، Docker، Redis، Elasticsearch، microservice. دستورالعمل بخش ۶ صریحاً منع کرده است.

## ۲. ساختار پوشه‌ها (تصمیم D3 — استاندارد Laravel)

```
app/
├── Enums/                    # ProjectStatus، ProductStatus، PublicationStatus، …
├── Filament/
│   ├── Resources/            # منابع پنل
│   ├── Pages/                # صفحات سفارشی پنل
│   └── Widgets/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   └── Public/           # Home، Biography، Project، Product، Company، Note، Journey، Contact
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Observers/                # پاک‌سازی morph، invalidation کش
├── Policies/
├── Providers/
│   ├── Filament/
│   └── ModuleServiceProvider.php
├── Services/
│   ├── Ai/                   # Adapter + اعتبارسنجی Base URL (ضد SSRF)
│   ├── Media/
│   ├── Publishing/           # انتشار، زمان‌بندی، invalidation کش
│   └── Seo/
└── Support/                  # کمک‌کننده‌های فارسی، slug، خواندن زمان

resources/
├── css/app.css               # توکن‌ها در @theme (یک محل)
├── js/app.js
└── views/
    ├── components/ui/        # اجزای کیت
    ├── components/layouts/   # app، admin
    ├── pages/                # صفحات عمومی
    ├── partials/             # header، footer، meta
    └── errors/

routes/
├── web.php                   # عمومی
├── admin.php                 # پنل (authed)
└── console.php               # زمان‌بند

tests/
├── Feature/Public/
├── Feature/Admin/
├── Feature/Publishing/
└── Unit/
```

## ۳. مدل دامنه — قطعی

### موجودیت‌ها و کلیدها

| موجودیت | جدول | Slug | Status | Visibility | Featured | Sort |
|---|---|---|---|---|---|---|
| Profile | `profiles` | — | `is_active` | — | — | — |
| Company | `companies` | ✓ | `status` | — | ✓ | ✓ |
| Product | `products` | ✓ | `status` | — | ✓ | ✓ |
| Project | `projects` | ✓ | `status` | ✓ | ✓ | ✓ |
| Note | `notes` | ✓ | `status` | ✓ | ✓ | ✓ |
| Experience | `experiences` | — | `status` | — | — | ✓ |
| Education | `education` | — | `status` | — | — | ✓ |
| Skill | `skills` | ✓ | `is_active` | — | — | ✓ |
| Technology | `technologies` | ✓ | `is_active` | — | — | ✓ |
| Achievement | `achievements` | — | `status` | — | ✓ | ✓ |
| Certificate | `certificates` | — | `status` | — | — | ✓ |
| TimelineEvent | `timeline_events` | — | `status` | ✓ | `is_highlight` | ✓ |
| CurrentFocus | `current_focuses` | — | `status` | — | ✓ | ✓ |
| Category | `categories` | ✓ | — | — | — | ✓ |
| Tag | `tags` | ✓ | — | — | — | — |
| Media | `media` | — | — | — | — | — |
| SeoMetadata | `seo_metadata` | — | — | — | — | — |
| ContactChannel | `contact_channels` | — | `is_active` | `is_public` | — | ✓ |
| Module | `modules` | `key` | `is_enabled` | — | — | ✓ |
| HomepageSection | `homepage_sections` | `key` | `is_enabled` | — | — | ✓ |
| Setting | `settings` | `key` | — | `is_public` | — | — |
| AiProvider | `ai_providers` | — | `is_enabled` | — | `is_default` | — |
| ContactMessage | `contact_messages` | — | — | — | — | — |

### مقادیر Statuses (Enumهای PHP backed)

```
PublicationStatus  : draft | published | archived              (پیش‌فرض: draft)
Visibility         : public | unlisted | private               (پیش‌فرض: private)
ProjectStatus      : idea | in_progress | completed | paused | archived
ProductStatus      : idea | development | beta | live | paused | archived
CompanyRelation    : founder | employee | collaborator | client | partner
TimelineEventType  : work | launch | learning | milestone | recognition | other
CurrentFocusType   : building | learning | exploring | goal
CurrentFocusStatus : active | paused | completed | archived
NoteType           : article | note | case_study | learning_log
ContactChannelType : email | website | social | phone | other
ContactPurpose     : professional | social | support | general
SettingType        : string | boolean | integer | json
EmploymentType     : full_time | part_time | contract | freelance | internship | volunteer
```

`MySQL enum` استفاده نمی‌شود؛ `string` + PHP backed enum (تصمیم Blueprint:59-61).

### روابط — قطعی

```
Profile      → belongsTo Media (avatar) · belongsTo Media (resume)
Company      hasMany Experience · belongsToMany Product  (company_product)
             belongsToMany Project  (company_project) · hasMany TimelineEvent (morph)
Product      belongsToMany Company · belongsToMany Project (product_project)
             belongsToMany Note (note_product)
Project      belongsToMany Product · belongsToMany Company · belongsToMany Skill (project_skill)
             belongsToMany Technology (project_technology) · belongsToMany Note (note_project)
Note         belongsToMany Tag · Category · Project · Product · Company
             belongsTo User (author, nullable) · morphMany SeoMetadata · morphMany MediaAttachment
TimelineEvent morphTo target
CurrentFocus belongsTo Project | Product | Company (max one, validated)
SeoMetadata  morphTo seoable · belongsTo Media (og image)
Media        hasMany MediaAttachment · belongsTo User (creator)
MediaAttachment morphTo mediable
```

### قواعد حذف (قطعی، طبق ERD:386-393)

| رابطه | رفتار |
|---|---|
| Pivotها | `cascadeOnDelete` — با حذف هر طرف، ردیف pivot حذف می‌شود |
| `experiences.company_id` | `nullOnDelete` — حذف شرکت نباید سابقه را پاک کند |
| `profiles.avatar_media_id` / `seo_metadata.og_image_media_id` | `nullOnDelete` |
| حذف Company/Product/Project/Note | **هرگز** به محتوای مرتبط cascade نمی‌کند؛ `archived` ترجیح دارد |
| حذف User مدیر | محتوای تألیف‌شده حذف نمی‌شود (`author_id` → null) |
| رسانه | ابتدا attachment، سپس فایل فیزیکی فقط اگر جای دیگری استفاده نشده |

### Soft Delete

فقط روی `projects`، `products`، `companies`، `notes` — با ترجیح `status=archived` در عمل. علت وجود `deleted_at`: امکان بازیابی حادثه‌ای طبق اصل «recoverability (archive over delete)».

## ۴. جدول مسیرها — قطعی

### عمومی

| متد | مسیر | کنترلر | نام |
|---|---|---|---|
| GET | `/` | HomeController | `home` |
| GET | `/biography` | BiographyController | `biography` |
| GET | `/biography/resume` | BiographyController@resume | `biography.resume` |
| GET | `/projects` | ProjectController@index | `projects.index` |
| GET | `/projects/{project:slug}` | ProjectController@show | `projects.show` |
| GET | `/products` | ProductController@index | `products.index` |
| GET | `/products/{product:slug}` | ProductController@show | `products.show` |
| GET | `/companies` | CompanyController@index | `companies.index` |
| GET | `/companies/{company:slug}` | CompanyController@show | `companies.show` |
| GET | `/notes` | NoteController@index | `notes.index` |
| GET | `/notes/{note:slug}` | NoteController@show | `notes.show` |
| GET | `/journey` | JourneyController | `journey` |
| GET | `/contact` | ContactController@show | `contact.show` |
| POST | `/contact` | ContactController@store | `contact.store` |
| GET | `/search` | SearchController | `search` |
| GET | `/feed` | FeedController | `feed` |
| GET | `/sitemap.xml` | SeoController@sitemap | `sitemap` |
| GET | `/robots.txt` | SeoController@robots | `robots` |

مسیرهای ماژول‌های خاموش با middleware `module:key` محافظت می‌شوند و **۴۰۴** می‌دهند (سیاست صریح، طبق CMS:220-223).

### پنل مدیریت

مسیر پایه `/admin` با احراز هویت اجباری، گروه‌بندی منطبق بر CMS Blueprint §3:

```
/admin
├── profile · biography · experiences · education · skills · technologies
├── projects · products · companies
├── notes · categories · tags
├── timeline · current-focus
├── homepage · modules · contact-channels
├── media · seo · settings
└── (ai — ساختار آماده، غیرفعال در MVP)
```

## ۵. دیزاین‌سیستم — توکن‌های قطعی

منبع: `UX_UI_Blueprint_UI_Kit_v1.0`. مقادیر رنگ UI Kit مبنا هستند، اما **کنتراست اصلاح‌شده** تا WCAG AA پاس شود.

| توکن | مقدار | نقش |
|---|---|---|
| `--color-bg` | `#FFFFFF` | پس‌زمینه |
| `--color-surface` | `#F8FAFC` | کارت و پنل |
| `--color-text` | `#0F172A` | متن اصلی |
| `--color-text-muted` | `#475569` | متن ثانویه |
| `--color-border` | `#E2E8F0` | مرز |
| `--color-accent` | `#2563EB` | لینک و اقدام |
| `--color-accent-hover` | `#1D4ED8` | هاور |
| `--color-success` | `#15803D` | موفقیت |
| `--color-warning` | `#B45309` | هشدار |
| `--color-danger` | `#B91C1C` | خطا |
| `--color-info` | `#0369A1` | اطلاع |

**تصحیح کنتراست (D11):** UI Kit می‌گوید مقادیر «نقطه‌ی شروع‌اند نه نهایی». `#2563EB` روی `#FFFFFF` نسبت ۵٫۱۷:۱ می‌دهد که AA را پاس می‌کند و بدون تغییر می‌ماند. برای متن روی سطح `#F8FAFC` نیز بررسی و تأیید شد.

**مقیاس تایپوگرافی** (موبایل → دسکتاپ):

| نقش | موبایل | دسکتاپ |
|---|---|---|
| Hero | 30–36px | 40–52px |
| عنوان صفحه | 26–32px | 32–40px |
| عنوان بخش | 22–26px | 24–30px |
| عنوان کارت | 18–20px | 18–22px |
| متن اصلی | 16–17px | 16–18px |
| متن کمکی | 14px | 14–15px |
| برچسب | 12–13px | 12–13px |

`line-height` متن فارسی: ۱٫۷ تا ۱٫۹. فونت: **Vazirmatn** (خودمیزبان، مجوز آزاد، وزن‌های کامل). فونت‌های `Inter`/`Instrument Sans` پیش‌فرض Laravel حذف می‌شوند.

**فاصله‌گذاری:** ۴، ۸، ۱۲، ۱۶، ۲۴، ۳۲، ۴۸، ۶۴–۹۶.
**شعاع:** کنترل ۸–۱۰px، کارت ۱۲–۱۶px.
**کانتینر:** ۱۱۲۰–۱۲۰۰px · **متن بلند:** ۶۸۰–۷۶۰px.
**Breakpoints:** ۳۲۰–۳۷۵ / ۳۹۰–۴۳۰ / ۷۶۸ / ۱۰۲۴–۱۲۸۰ / ۱۴۴۰+.

**بدون** گرادیان سنگین، سایه روی همه‌چیز، انیمیشن نمایشی، چارت تزئینی، آمار ساختگی.

## ۶. سیاست امنیت — قطعی

| موضوع | سیاست |
|---|---|
| CSRF | فعال روی همه‌ی POSTها (پیش‌فرض Laravel) |
| XSS | تمام HTML ورودی پنل با HTMLPurifier پاک‌سازی می‌شود؛ Blade به‌صورت پیش‌فرض escape می‌کند |
| Mass Assignment | `$fillable` صریح روی همه‌ی مدل‌ها |
| Authorization | Policy برای هر موجودیت + `Gate`؛ کنترل در سرور نه در UI |
| ورود پنل | اجباری روی `/admin`؛ **فهرست مجاز ایمیل مدیر** از `ADMIN_EMAIL` |
| Rate Limiting | ورود: ۵/دقیقه · فرم تماس: ۵/ساعت به‌ازای IP · جست‌وجو: ۳۰/دقیقه |
| Upload | اعتبارسنجی MIME واقعی + پسوند، سقف ۵ مگابایت، نام تصادفی، جلوگیری از اجرا |
| Secrets | کلید AI با `encrypted` cast؛ هرگز در HTML/JS/Log/پاسخ API |
| SSRF | اعتبارسنجی Base URL: فقط `https`، رد آدرس‌های خصوصی/داخلی، بدون ریدایرکت |
| هدرها | CSP، HSTS، `X-Frame-Options: DENY`، `X-Content-Type-Options: nosniff`، `Referrer-Policy`، `Permissions-Policy` |
| Session | `secure` + `httpOnly` + `sameSite=lax` + `SESSION_ENCRYPT=true` در تولید |
| خطا | `APP_DEBUG=false` در تولید؛ صفحات ۴۰۴/۴۰۳/۵۰۰/۴۱۹/۴۲۹/۵۰۳ سفارشی |
| .env | بیرون docroot، در `.gitignore`، مجوز ۶۰۰ |
| Preview | مسیر پیش‌نمایش نیازمند احراز هویت (بدون توکن قابل حدس) |
| Indexing پنل | `noindex` روی `/admin` و مسیرهای پیش‌نمایش |

## ۷. AI Layer — قطعی

ساختار کامل ساخته می‌شود، اما **در MVP غیرفعال** است (طبق CMS:255-256 و Blueprint:775-777 که فعال‌سازی را مشروط کرده‌اند).

```
Admin → AiService → ProviderAdapter (Interface) → OpenAI-compatible endpoint
                     ├── OpenAiCompatibleAdapter
                     └── NullAdapter           (وقتی تنظیمات نیست — بدون خطا کار می‌کند)
```

- جدول `ai_providers` با `api_key` رمزنگاری‌شده و `is_default` یکتا (با transaction).
- بدون کلید واقعی، سرویس با `NullAdapter` بی‌صدا کار می‌کند و هیچ درخواست خارجی نمی‌زند.
- AI هرگز مستقیم منتشر نمی‌کند؛ خروجی فقط پیشنهاد است (Human-in-the-loop).
- اعتبارسنجی Base URL پیش از هر درخواست، برای جلوگیری از SSRF.

## ۸. طرح زمان‌بند

| کار | زمان | فرمان |
|---|---|---|
| انتشار محتوای زمان‌بندی‌شده | هر دقیقه | `contents:publish-scheduled` |
| پاک‌سازی پیام‌های تماس اسپم‌قدیمی | روزانه | `contact:prune-spam` |
| پاک‌سازی کش منقضی | ساعتی | `cache:prune` |

روی هاست: یک cron با `schedule:run` هر دقیقه.

## ۹. معیار پذیرش (DoD) هر فاز

هر فاز تا زمانی تکمیل نیست که:

1. `php artisan test` سبز باشد.
2. `php vendor/bin/pint --test` بدون خطا باشد.
3. صفحات جدید در ۳۲۰px، ۷۶۸px و ۱۴۴۰px بدون شکست رندر شوند.
4. هیچ محتوای ساختگی در خروجی عمومی نباشد.
5. دسترسی‌های سرور-ساید تست شده باشد (نه فقط پنهان‌کردن دکمه).
6. `docs/implementation/` با تصمیم‌های جدید به‌روز شده باشد.

## ۱۰. محدودیت‌های ثبت‌شده

| محدودیت | اثر | جایگزین |
|---|---|---|
| MySQL client محلی نیست | تست روی SQLite انجام می‌شود | سازگاری MySQL هنگام دیپلوی با اجرای واقعی migration تأیید می‌شود |
| سایت مرجع `hosseintaheri.ir` بررسی مستقیم نشد | الهام بصری از مستندات UI Kit گرفته شد | مستندات UI Kit جامع‌تر از یک بازدید سطحی است |
| بدون worker/Redis روی هاست | صف sync، کش database | همه‌چیز با قابلیت‌های پایه Laravel |
| AI بدون کلید واقعی | سرویس غیرفعال | ساختار آماده + راهنمای تنظیم |
