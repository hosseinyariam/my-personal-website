# Laravel Models & Migration Blueprint v1.0

## پروژه: hosseinyariam.ir

**وضعیت سند:** طراحی پیشنهادی برای بازبینی --- هنوز مجوز اجرای Migration
یا تغییر دیتابیس نیست\
**نسخه معماری:** Laravel 12 / PHP 8.4 / MySQL / Blade / Tailwind CSS /
Vite\
**زبان اصلی:** فارسی (`fa`) --- معماری آماده توسعه زبان انگلیسی (`en`)\
**الگوی معماری:** Modular Monolith

------------------------------------------------------------------------

## 1. هدف سند

این سند، مدل دامنه و ERD پیشنهادی را به نقشه‌ای قابل پیاده‌سازی در Laravel
تبدیل می‌کند. برای هر مدل، مسئولیت، فیلدهای پیشنهادی، روابط Eloquent،
ملاحظات Migration و قواعد داده مشخص شده‌اند.

این سند هنوز کد Migration نهایی نیست. قبل از کدنویسی باید نام‌گذاری،
راهبرد ترجمه، نحوه اتصال رسانه‌ها و شکل رابطه‌ی رویدادهای Timeline نهایی
شوند.

## 2. اصول طراحی

1.  **شروع ساده، توسعه‌پذیر:** Modular Monolith؛ بدون Microservice و
    پیچیدگی غیرضروری.
2.  **رکورد، نه کد اختصاصی:** افزودن شرکت، محصول، پروژه یا یادداشت جدید
    باید عمدتاً از پنل مدیریت و با ساخت رکورد انجام شود.
3.  **محتوا از نمایش جداست:** موجودیت‌های محتوایی نباید مستقیماً وابسته به
    یک صفحه یا کامپوننت خاص باشند.
4.  **روابط روشن:** روابط اصلی با Foreign Key واقعی مدل می‌شوند؛ رابطه‌ی
    polymorphic فقط جایی استفاده می‌شود که نیاز واقعی وجود داشته باشد.
5.  **حذف ایمن:** حذف یک شرکت نباید به‌صورت ناخواسته پروژه‌ها، محصولات یا
    یادداشت‌های مرتبط را حذف کند.
6.  **انتشار قابل‌کنترل:** محتوای عمومی از پیش‌نویس و محتوای آرشیوی تفکیک
    شود.
7.  **امنیت:** کلیدهای AI و اسرار در جدول تنظیمات عمومی یا پاسخ API قابل
    مشاهده نباشند.
8.  **مهاجرت‌پذیری:** تغییرات دیتابیس به Migrationهای کوچک، مرتب و قابل
    بازبینی تقسیم شوند.
9.  **عدم ساخت قابلیت‌های فرضی:** در MVP فقط فیلدهایی اضافه شوند که
    کاربرد مشخص دارند.
10. **بدون حذف دیتابیس فعلی در این مرحله:** پروژه‌ی جدید جدا ساخته می‌شود؛
    حذف یا جایگزینی محیط قبلی تنها پس از تست و تأیید جداگانه انجام خواهد
    شد.

------------------------------------------------------------------------

## 3. قواعد عمومی نام‌گذاری و نوع داده

-   نام مدل‌ها: `StudlyCase` مفرد؛ مانند `CurrentFocus`.
-   نام جدول‌ها: `snake_case` جمع؛ مانند `current_focuses`.
-   کلید اصلی: `id` از نوع `bigint` با `$table->id()`.
-   Foreign Keyها: `foreignId()` همراه با constraint و سیاست حذف صریح.
-   تاریخ‌ها: `date` برای تاریخ بدون زمان، `dateTime`/`timestamp` برای
    رخداد زمان‌دار؛ در Laravel زمان‌ها به‌صورت UTC ذخیره و هنگام نمایش به
    منطقه زمانی تنظیم‌شده تبدیل شوند.
-   وضعیت‌ها: برای فیلدهای ساده از `string` محدود و PHP backed enum در
    لایه برنامه استفاده شود؛ استفاده از MySQL `enum` برای وضعیت‌هایی که
    ممکن است تغییر کنند توصیه نمی‌شود.
-   متن کوتاه: `string`; توضیح چندخطی: `text`; محتوای طولانی: `longText`
    فقط در صورت نیاز.
-   داده ساختاریافته: `json` تنها برای داده‌هایی که واقعاً ساختار متغیر
    دارند، نه برای جایگزین کردن جدول رابطه‌ای.
-   Slug: `string(180)` با index یکتا در محدوده‌ی تعریف‌شده.
-   ترتیب نمایش: `unsignedInteger sort_order` با مقدار پیش‌فرض صفر.
-   وضعیت فعال‌بودن: فقط در جاهایی که غیرفعال‌سازی مستقل از انتشار لازم
    است؛ از تکثیر بی‌دلیل `is_active`, `status`, `published_at` پرهیز
    شود.
-   اکثر مدل‌های محتوایی: `timestamps()`؛ `softDeletes()` فقط اگر بازیابی
    محتوا و سابقه حذف ارزش عملی دارد.
-   فیلدهای قابل‌نمایش عمومی باید در Policy/Query و Resourceهای پاسخ API
    کنترل شوند؛ مخفی کردن فیلد در فرم به‌تنهایی کافی نیست.

## 4. راهبرد انتشار محتوا

برای مدل‌هایی که محتوای قابل انتشار هستند، الگوی پیشنهادی:

-   `status`: `draft`, `published`, `archived`
-   `published_at`: nullable datetime
-   `is_featured`: فقط برای محتوایی که می‌تواند در جایگاه ویژه نمایش داده
    شود
-   `sort_order`: فقط در فهرست‌هایی که ترتیب دستی لازم دارند
-   `deleted_at`: فقط اگر soft delete برای بازیابی یا جلوگیری از حذف
    تصادفی نیاز است

قاعده انتشار: - محتوای عمومی باید `status = published` داشته باشد. - اگر
زمان انتشار در آینده باشد، تا رسیدن زمان تعیین‌شده منتشر نشود. - انتشار و
لغو انتشار باید از یک سرویس/Action واحد انجام شود تا قواعد در چند
Controller تکرار نشوند. - وضعیت `archived` یعنی محتوا از مسیرهای عمومی
معمول حذف می‌شود اما برای سابقه و پنل مدیریت باقی می‌ماند.

------------------------------------------------------------------------

## 5. مدل‌ها و فیلدهای پیشنهادی

### 5.1 `User`

**جدول:** `users`\
**مسئولیت:** ورود به پنل مدیریت. در MVP فقط حساب مدیر لازم است؛ سیستم
چندنقشی پیچیده ایجاد نشود مگر نیاز واقعی شکل بگیرد.

  فیلد                نوع پیشنهادی         قاعده
  ------------------- -------------------- -------------------------
  id                  bigint               Primary Key
  name                string(120)          required
  email               string(190)          unique
  email_verified_at   timestamp nullable   در صورت استفاده
  password            string               hash شده؛ هرگز متن ساده
  remember_token      string nullable      استاندارد Laravel
  timestamps          ---                  استاندارد Laravel

**روابط:** `User` در صورت نیاز به ثبت نویسنده می‌تواند با `Note` رابطه‌ی
`hasMany` داشته باشد. در MVP می‌توان `user_id` را برای محتوا nullable نگه
داشت یا اصلاً اضافه نکرد، اگر تنها یک نویسنده وجود دارد و نیازی به انتساب
نیست.

**امنیت:** مدل باید فیلدهای حساس را در `$hidden` نگه دارد. حساب مدیر با
Seeder امن و رمز از محیط/فرآیند امن ساخته شود؛ رمز ثابت در Repository
قرار نگیرد.

### 5.2 `Profile`

**جدول:** `profiles`\
**مسئولیت:** اطلاعات هویتی و معرفی اصلی فرد. این مدل با `User` یکی نیست؛
حساب ورود از هویت عمومی جداست.

  -----------------------------------------------------------------------
  فیلد                    نوع پیشنهادی            قاعده
  ----------------------- ----------------------- -----------------------
  id                      bigint                  PK

  display_name            string(160)             required

  professional_title      string(200) nullable    عنوان حرفه‌ای

  short_bio               text nullable           معرفی کوتاه

  biography               longText nullable       متن کامل

  location_label          string(160) nullable    برچسب عمومی مکان؛ نه
                                                  اطلاعات خصوصی

  email_public            string(190) nullable    فقط در صورت تمایل به
                                                  انتشار

  availability_status     string(40) nullable     وضعیت همکاری/دسترسی

  resume_file_path        string(255) nullable    مسیر فایل، نه محتوای
                                                  فایل

  avatar_media_id         FK nullable             فقط اگر Media مدل رابطه
                                                  مستقیم داشته باشد

  is_active               boolean                 یک پروفایل فعال در MVP

  timestamps              ---                     ---
  -----------------------------------------------------------------------

**نکته:** چون معمولاً یک پروفایل اصلی وجود دارد، در لایه برنامه قاعده‌ی
«یک پروفایل فعال» اعمال شود. اگر نیاز به چند زبان برای biography وجود
دارد، متن ترجمه نباید با کپی کامل رکوردهای پروفایل مدیریت شود؛ تصمیم
ترجمه در بخش 9 آمده است.

### 5.3 `Company`

**جدول:** `companies`\
**مسئولیت:** شرکت‌ها، تیم‌ها یا سازمان‌هایی که در مسیر حرفه‌ای کاربر نقش
داشته‌اند.

  --------------------------------------------------------------------------
  فیلد                    نوع پیشنهادی            قاعده
  ----------------------- ----------------------- --------------------------
  id                      bigint                  PK

  name                    string(180)             required

  slug                    string(180)             unique

  summary                 text nullable           خلاصه

  description             longText nullable       معرفی کامل

  website_url             string(2048) nullable   URL معتبر

  relationship_type       string(40) nullable     مانند founder, employee,
                                                  collaborator, client

  start_date              date nullable           ---

  end_date                date nullable           باید بعد از start_date
                                                  باشد

  status                  string(30)              draft/published/archived

  published_at            timestamp nullable      ---

  is_featured             boolean                 پیش‌فرض false

  sort_order              unsignedInteger         پیش‌فرض 0

  timestamps              ---                     ---

  deleted_at              timestamp nullable      در صورت تأیید نیاز به
                                                  بازیابی
  --------------------------------------------------------------------------

**روابط:** `hasMany` با Experiences، و در صورت انتخاب رابطه مستقیم
`belongsToMany` با Products و Projects از طریق pivotهای مربوط.

**حذف:** حذف شرکت نباید به حذف آبشاری پروژه‌ها یا محصولات منجر شود. در
حالت پیش‌فرض آرشیوکردن امن‌تر از حذف است.

### 5.4 `Product`

**جدول:** `products`\
**مسئولیت:** محصول یا پلتفرم مستقلی که ارزش و وضعیت خودش را دارد؛ با
پروژه‌ی فنی یکی نیست.

  -----------------------------------------------------------------------
  فیلد                    نوع پیشنهادی            قاعده
  ----------------------- ----------------------- -----------------------
  id                      bigint                  PK

  name                    string(180)             required

  slug                    string(180)             unique

  tagline                 string(220) nullable    ---

  summary                 text nullable           ---

  description             longText nullable       ---

  problem                 text nullable           مسئله‌ای که محصول هدف
                                                  گرفته

  solution                text nullable           راه‌حل پیشنهادی

  current_state           text nullable           وضعیت فعلی واقعی

  status                  string(30)              idea, development,
                                                  beta, live, paused,
                                                  archived

  website_url             string(2048) nullable   ---

  repository_url          string(2048) nullable   فقط اگر عمومی باشد

  launched_at             date nullable           ---

  published_at            timestamp nullable      زمان انتشار در سایت

  is_featured             boolean                 ---

  sort_order              unsignedInteger         ---

  timestamps              ---                     ---

  deleted_at              timestamp nullable      در صورت نیاز
  -----------------------------------------------------------------------

**روابط:** `belongsToMany(Company)` از طریق `company_product`;
`belongsToMany(Project)` از طریق `product_project`;
`belongsToMany(Note)` از طریق `note_product`.

**تصمیم مهم:** محصول ممکن است از چند پروژه تشکیل شود و یک پروژه هم
می‌تواند به چند محصول کمک کند؛ بنابراین رابطه‌ی Product--Project در این
Blueprint چندبه‌چند است، نه الزاماً `product_id` مستقیم در جدول پروژه‌ها.

### 5.5 `Project`

**جدول:** `projects`\
**مسئولیت:** پروژه‌ای که برای حل یک مسئله یا ساخت قابلیت/محصول انجام شده
است.

  فیلد              نوع پیشنهادی            قاعده
  ----------------- ----------------------- ------------------------------------------------
  id                bigint                  PK
  title             string(180)             required
  slug              string(180)             unique
  summary           text nullable           ---
  description       longText nullable       ---
  problem           text nullable           مسئله
  context           text nullable           زمینه
  role              string(180) nullable    نقش فرد
  contribution      longText nullable       مشارکت واقعی
  outcome           longText nullable       نتیجه‌ی قابل اتکا
  lessons_learned   longText nullable       آموخته‌ها
  status            string(30)              idea, in_progress, completed, paused, archived
  visibility        string(20)              public, unlisted, private
  started_at        date nullable           ---
  completed_at      date nullable           ---
  repository_url    string(2048) nullable   ---
  live_url          string(2048) nullable   ---
  published_at      timestamp nullable      ---
  is_featured       boolean                 ---
  sort_order        unsignedInteger         ---
  timestamps        ---                     ---
  deleted_at        timestamp nullable      در صورت نیاز

**روابط:** `belongsToMany(Product)`, `belongsToMany(Company)`,
`belongsToMany(Skill)`, `belongsToMany(Technology)`,
`belongsToMany(Note)`.

**اعتبارسنجی:** `completed_at` نباید قبل از `started_at` باشد. آدرس‌های
Repository و Live URL باید معتبر باشند. ادعاهای نتیجه و اثر باید واقعی و
قابل دفاع باشند.

### 5.6 `Experience`

**جدول:** `experiences`\
**مسئولیت:** سابقه‌ی شغلی/همکاری در یک شرکت یا سازمان؛ جدا از پروژه‌ها.

فیلدها: - `id` - `company_id` nullable FK - `title` string(180),
required - `employment_type` string(40) nullable - `location_label`
string(160) nullable - `description` text nullable - `started_at` date
nullable - `ended_at` date nullable - `is_current` boolean default
false - `sort_order` unsignedInteger default 0 - `timestamps`

**روابط:** `belongsTo(Company)`. هنگام حذف شرکت، `company_id` به `null`
تبدیل شود یا حذف شرکت مسدود شود؛ حذف آبشاری سابقه‌ی شغلی توصیه نمی‌شود.

### 5.7 `Education`

**جدول:** `education`\
**مسئولیت:** سوابق تحصیلی.

فیلدها: - `id` - `institution` string(180), required - `degree`
string(160) nullable - `field_of_study` string(180) nullable -
`description` text nullable - `started_at` date nullable - `ended_at`
date nullable - `is_current` boolean default false - `sort_order`
unsignedInteger default 0 - `timestamps`

در صورت نیاز آینده می‌توان `institution_url` افزود؛ فعلاً فقط در صورت
کاربرد واقعی.

### 5.8 `Skill`

**جدول:** `skills`\
**مسئولیت:** توانمندی‌های حرفه‌ای، با امکان گروه‌بندی ساده.

فیلدها: - `id` - `name` string(120), required - `slug` string(140),
unique - `description` text nullable - `category` string(80) nullable -
`proficiency_label` string(40) nullable --- برچسب کیفی، نه ادعای عددی
ساختگی - `sort_order` unsignedInteger default 0 - `is_active` boolean
default true - `timestamps`

**روابط:** `belongsToMany(Project)` از طریق `project_skill`.

### 5.9 `Technology`

**جدول:** `technologies`\
**مسئولیت:** فناوری‌ها و ابزارهایی که در پروژه‌ها استفاده شده‌اند.

فیلدها: - `id` - `name` string(120), required - `slug` string(140),
unique - `category` string(80) nullable - `website_url` string(2048)
nullable - `icon_key` string(80) nullable --- شناسه‌ی آیکون از مجموعه‌ی
مجاز - `sort_order` unsignedInteger default 0 - `is_active` boolean
default true - `timestamps`

**روابط:** `belongsToMany(Project)` از طریق `project_technology`.

### 5.10 `Note`

**جدول:** `notes`\
**مسئولیت:** مقاله، یادداشت، تجربه، یادگیری، روایت ساخت محصول یا تحلیل
مسئله.

فیلدها: - `id` - `title` string(220), required - `slug` string(220),
unique - `excerpt` text nullable - `body` longText, required - `type`
string(40) --- article, note, case_study, learning_log - `status`
string(30) --- draft, published, archived - `visibility` string(20) ---
public, unlisted, private - `published_at` timestamp nullable -
`reading_time_minutes` unsignedSmallInteger nullable --- قابل
محاسبه/بازسازی؛ منبع حقیقت نباشد - `author_id` FK nullable, اگر انتساب
نویسنده لازم باشد - `is_featured` boolean default false - `sort_order`
unsignedInteger default 0 - `timestamps` - `deleted_at` nullable در صورت
نیاز به بازیابی

**روابط:** `belongsToMany(Tag)`, `belongsToMany(Category)`,
`belongsToMany(Project)`, `belongsToMany(Product)`,
`belongsToMany(Company)`.

**نکته:** محتوای Markdown یا HTML باید با سیاست مشخص رندر شود؛ محتوای
HTML واردشده باید sanitize شود. فایل‌های پیوست از بدنه‌ی متن جدا نگهداری
شوند.

### 5.11 `Category`

**جدول:** `categories`\
**مسئولیت:** دسته‌بندی یادداشت‌ها.

فیلدها: - `id` - `name` string(120), required - `slug` string(140),
unique - `description` text nullable - `sort_order` unsignedInteger
default 0 - `timestamps`

در MVP دسته‌بندی سلسله‌مراتبی ضروری نیست؛ اگر لازم شد، `parent_id`
nullable با سیاست حذف `nullOnDelete` اضافه می‌شود.

### 5.12 `Tag`

**جدول:** `tags`\
**مسئولیت:** برچسب‌های آزاد برای یادداشت‌ها.

فیلدها: - `id` - `name` string(100), required - `slug` string(120),
unique - `timestamps`

### 5.13 `TimelineEvent`

**جدول:** `timeline_events`\
**مسئولیت:** رخدادهای مهم مسیر حرفه‌ای؛ مثلاً شروع محصول، پذیرش در برنامه،
انتشار نسخه یا یادگیری مهم.

فیلدها: - `id` - `title` string(180), required - `summary` text
nullable - `event_type` string(40) --- work, launch, learning,
milestone, recognition, other - `occurred_at` date nullable - `status`
string(30) --- draft, published, archived - `visibility` string(20) ---
public, unlisted, private - `published_at` timestamp nullable -
`sort_order` unsignedInteger default 0 - `timestamps`

**تصمیم رابطه:** برای MVP، از یک رابطه‌ی polymorphic به نام
`timelineable` استفاده می‌شود تا رخداد در صورت نیاز به یک موجودیت اصلی
(Project/Product/Company/Note) متصل شود: - `timelineable_type` nullable
string - `timelineable_id` nullable bigint - index مرکب روی
`timelineable_type`, `timelineable_id`

هر رویداد حداکثر یک موجودیت اصلی دارد. اگر بعداً نیاز به چند موجودیت در
یک رخداد پیدا شد، pivot اختصاصی اضافه می‌شود؛ از ابتدا سیستم چند-رابطه‌ای
پیچیده ساخته نمی‌شود.

### 5.14 `CurrentFocus`

**جدول:** `current_focuses`\
**مسئولیت:** نمایش آنچه اکنون روی آن کار می‌شود یا در حال یادگیری/بررسی
است؛ یکی از عناصر اصلی Living Profile.

فیلدها: - `id` - `title` string(180), required - `description` text
nullable - `focus_type` string(30) --- building, learning, exploring,
goal - `status` string(30) --- active, paused, completed, archived -
`started_at` date nullable - `target_date` date nullable - `sort_order`
unsignedInteger default 0 - `is_featured` boolean default false -
`project_id` nullable FK - `product_id` nullable FK - `company_id`
nullable FK - `timestamps`

**قاعده:** در MVP رابطه‌های صریح nullable بهتر از polymorphic هستند، چون
سه نوع هدف اصلی روشن‌اند و FKها integrity ایجاد می‌کنند. حداکثر یکی از
`project_id`, `product_id`, `company_id` باید مقدار داشته باشد؛ این
قاعده با validation در برنامه enforce شود. در صورت نیاز به اتصال به Note
یا انواع جدید، بعداً توسعه داده می‌شود.

### 5.15 `Media`

**جدول:** `media`\
**مسئولیت:** ثبت فراداده‌ی فایل‌های رسانه‌ای؛ خود فایل در storage نگهداری
می‌شود.

فیلدها: - `id` - `disk` string(40), required --- مثلاً public - `path`
string(1024), required - `original_name` string(255) nullable -
`mime_type` string(120) nullable - `size_bytes` unsignedBigInteger
nullable - `alt_text` string(255) nullable - `caption` text nullable -
`width` unsignedInteger nullable - `height` unsignedInteger nullable -
`created_by` nullable FK به users، در صورت نیاز - `timestamps`

**اتصال به محتوا:** استفاده از `media_attachments` با رابطه‌ی
polymorphic، برای اینکه یک رسانه بتواند به چند موجودیت متصل شود. Pivot
باید شامل `media_id`, `mediable_type`, `mediable_id`, `role`,
`sort_order` باشد و indexهای لازم داشته باشد. برای MVP، حذف فایل فیزیکی
باید از طریق سرویس مدیریت رسانه انجام شود، نه مستقیماً داخل event مدل؛
حذف رکورد نباید فایل مرتبط با رکورد دیگری را ناخواسته حذف کند.

### 5.16 `SeoMetadata`

**جدول:** `seo_metadata`\
**مسئولیت:** تنظیمات SEO برای صفحات و موجودیت‌های محتوایی.

فیلدها: - `id` - `seoable_type` string - `seoable_id` bigint -
`meta_title` string(220) nullable - `meta_description` string(320)
nullable - `canonical_url` string(2048) nullable - `robots` string(80)
nullable - `og_title` string(220) nullable - `og_description` text
nullable - `og_image_media_id` nullable FK به media - `timestamps`

**روابط:** polymorphic `morphTo`؛ index مرکب روی `seoable_type`,
`seoable_id`. برای هر موجودیت حداکثر یک رکورد SEO مجاز باشد (unique
composite). مقادیر SEO از محتوای کاربر تولید یا fallback می‌شوند؛ این
جدول نباید به منبع اصلی عنوان/متن محتوا تبدیل شود.

### 5.17 `ContactChannel`

**جدول:** `contact_channels`\
**مسئولیت:** مسیرهای ارتباطی و لینک‌های اجتماعی که در سایت نمایش داده
می‌شوند.

فیلدها: - `id` - `label` string(100), required - `channel_type`
string(40) --- email, website, social, phone, other - `url`
string(2048), required - `handle` string(180) nullable - `purpose`
string(40) nullable --- professional, social, support, general -
`is_public` boolean default true - `is_active` boolean default true -
`sort_order` unsignedInteger default 0 - `timestamps`

**امنیت:** شماره/ایمیل خصوصی نباید به‌عنوان پیش‌فرض وارد شود. فقط
کانال‌هایی که `is_public = true` و `is_active = true` هستند در صفحه عمومی
نمایش داده شوند.

### 5.18 `Module`

**جدول:** `modules`\
**مسئولیت:** فعال/غیرفعال کردن قابلیت‌ها و بخش‌های سطح بالای سایت.

فیلدها: - `id` - `key` string(80), unique --- مثل `projects`,
`products`, `timeline`, `notes` - `name` string(120) - `description`
text nullable - `is_enabled` boolean default true - `settings` json
nullable --- فقط تنظیمات نمایشی محدود و غیرحساس - `sort_order`
unsignedInteger default 0 - `timestamps`

**قاعده:** رکورد Module نباید جایگزین authorization یا بررسی امنیتی شود.
خاموش بودن ماژول یعنی مسیر/بخش عمومی آن غیرفعال شود؛ دسترسی مدیر همچنان
باید با middleware/policy کنترل شود.

### 5.19 `HomepageSection`

**جدول:** `homepage_sections`\
**مسئولیت:** ترتیب و تنظیم نمایش بخش‌های صفحه اصلی Living Profile.

فیلدها: - `id` - `key` string(80), unique --- مثل `hero`,
`current_focus`, `featured_projects` - `title` string(160) nullable -
`is_enabled` boolean default true - `settings` json nullable --- تنظیمات
نمایشی whitelist شده، بدون کلید یا اسرار - `sort_order` unsignedInteger
default 0 - `timestamps`

این جدول فقط ترتیب و پیکربندی بخش‌ها را نگه می‌دارد؛ داده‌ی پروژه‌ها،
محصولات و یادداشت‌ها در جدول‌های اصلی باقی می‌ماند.

### 5.20 `Setting`

**جدول:** `settings`\
**مسئولیت:** تنظیمات عمومی سایت که از پنل مدیریت تغییر می‌کنند.

فیلدهای پیشنهادی: - `id` - `key` string(160), unique - `value` longText
nullable - `type` string(30) --- string, boolean, integer, json -
`group` string(80) nullable - `is_public` boolean default false -
`timestamps`

**نکته:** این جدول برای مقادیر عمومی/غیرمحرمانه است. API key، token،
password یا secret نباید در `settings.value` به‌صورت معمول یا plaintext
ذخیره شود. تنظیمات محرمانه‌ی AI باید در مدل/سرویس اختصاصی و به‌شکل
رمزگذاری‌شده ذخیره شود.

### 5.21 `AiProvider` (اختیاری برای MVP، آماده‌سازی مرحله‌ای)

**جدول:** `ai_providers`\
**مسئولیت:** تنظیمات اتصال به ارائه‌دهنده‌ی AI. فقط زمانی پیاده‌سازی شود که
قابلیت AI واقعاً وارد scope شده باشد.

فیلدها: - `id` - `name` string(120) - `provider_key` string(80) -
`base_url` string(2048) nullable - `model_name` string(160) - `api_key`
text nullable --- فقط مقدار رمزگذاری‌شده با Laravel encrypted cast یا
encryption service - `is_enabled` boolean default false - `is_default`
boolean default false - `options` json nullable --- بدون secret -
`timestamps`

**قواعد حیاتی:** - کلید خام در log، exception، response، HTML، JS،
export یا فرم عمومی ظاهر نشود. - API key در درخواست‌های `GET` مجدداً نمایش
داده نشود؛ رابط کاربری فقط وضعیت «تنظیم شده/تنظیم نشده» را نشان دهد. -
در حالت ذخیره‌ی کلید جدید، مقدار جدید اعتبارسنجی و رمزگذاری شود. - فقط
backend مجاز به استفاده از کلید است. - فقط یک provider می‌تواند
`is_default = true` باشد؛ این قاعده با transaction و validation اعمال
شود. - اگر AI در MVP فعال نیست، این Migration را به فاز بعد موکول کنید.

------------------------------------------------------------------------

## 6. Pivot Tables و روابط چندبه‌چند

تمام pivotها باید کلیدهای خارجی معتبر، `timestamps` فقط در صورت نیاز و
unique composite index داشته باشند.

  -----------------------------------------------------------------------
  جدول                    ستون‌ها                  قید یکتا / سیاست
  ----------------------- ----------------------- -----------------------
  `company_product`       `company_id`,           unique جفت؛ حذف pivot
                          `product_id`            با حذف عضو مجاز

  `company_project`       `company_id`,           unique جفت
                          `project_id`            

  `product_project`       `product_id`,           unique جفت
                          `project_id`            

  `project_skill`         `project_id`,           unique جفت
                          `skill_id`              

  `project_technology`    `project_id`,           unique جفت
                          `technology_id`         

  `note_tag`              `note_id`, `tag_id`     unique جفت

  `note_category`         `note_id`,              unique جفت
                          `category_id`           

  `note_project`          `note_id`, `project_id` unique جفت

  `note_product`          `note_id`, `product_id` unique جفت

  `note_company`          `note_id`, `company_id` unique جفت

  `media_attachments`     `media_id`,             unique یا قید تکرار
                          `mediable_type`,        متناسب با role؛ index
                          `mediable_id`, `role`,  روی morph
                          `sort_order`            

  `timeline_events`       `timelineable_type`,    یک هدف اصلی برای هر
                          `timelineable_id`       رویداد

  `seo_metadata`          `seoable_type`,         unique جفت برای یک
                          `seoable_id`            رکورد SEO در هر هدف
  -----------------------------------------------------------------------

برای pivotهای ساده، از `belongsToMany()` استفاده شود. اگر pivot بعدها
فیلدهای دامنه‌ای مستقل (مثلاً نقش شرکت در محصول) پیدا کرد، آن را به مدل
اختصاصی تبدیل کنید.

------------------------------------------------------------------------

## 7. خلاصه روابط Eloquent

  -----------------------------------------------------------------------------
  مدل                     رابطه                         نوع
  ----------------------- ----------------------------- -----------------------
  Profile                 Media (avatar)                belongsTo یا اتصال از
                                                        طریق media attachment

  Company                 Products                      belongsToMany

  Company                 Projects                      belongsToMany

  Company                 Experiences                   hasMany

  Product                 Companies                     belongsToMany

  Product                 Projects                      belongsToMany

  Product                 Notes                         belongsToMany

  Project                 Products                      belongsToMany

  Project                 Companies                     belongsToMany

  Project                 Skills                        belongsToMany

  Project                 Technologies                  belongsToMany

  Project                 Notes                         belongsToMany

  Note                    Tags                          belongsToMany

  Note                    Categories                    belongsToMany

  Note                    Projects/Products/Companies   belongsToMany

  TimelineEvent           Timelineable                  morphTo

  SeoMetadata             Seoable                       morphTo

  MediaAttachment         Mediable                      morphTo

  CurrentFocus            Project/Product/Company       belongsTo (nullable)

  Experience              Company                       belongsTo

  Project                 Media                         morphMany through
                                                        attachment model

  Note                    Media                         morphMany through
                                                        attachment model
  -----------------------------------------------------------------------------

**نکته‌ی پیاده‌سازی:** در رابطه‌ی `MediaAttachment`، اگر بخواهیم از APIهای
Eloquent مرتب و قابل نگهداری استفاده کنیم، مدل واسط `MediaAttachment`
بهتر از استفاده‌ی پراکنده از morph pivot خام است. در فاز پیاده‌سازی، API
نهایی رابطه‌ها باید با نسخه‌ی Laravel و تست‌ها تثبیت شود.

------------------------------------------------------------------------

## 8. Migration Blueprint و ترتیب ساخت

ترتیب پیشنهادی برای کاهش مشکل Foreign Key:

1.  **Core:** `users`, `profiles`
2.  **Taxonomy:** `categories`, `tags`, `skills`, `technologies`
3.  **Organizations and portfolio:** `companies`, `products`, `projects`
4.  **Career:** `experiences`, `education`
5.  **Content:** `notes`
6.  **Pivots:** تمام pivotهای
    Company/Product/Project/Note/Skill/Technology
7.  **Profile presentation:** `current_focuses`, `timeline_events`,
    `contact_channels`
8.  **Media:** `media`, `media_attachments`
9.  **SEO:** `seo_metadata`
10. **CMS presentation:** `modules`, `homepage_sections`, `settings`
11. **Optional integrations:** `ai_providers`
12. **Indexes and constraints:** در Migrationهای اولیه هر جدول تا حد
    ممکن کنار همان جدول تعریف شوند؛ Migration جدا فقط وقتی لازم است که
    وابستگی یا تغییر بعدی وجود داشته باشد.

هر Migration باید: - یک تغییر منطقی روشن انجام دهد؛ - `up()` و `down()`
معتبر داشته باشد؛ - در محیط توسعه با دیتابیس خالی تست شود؛ - روی نسخه‌ی
MySQL هاست پشتیبانی‌شده اجرا شود؛ - از حذف/تغییر مخرب داده بدون تأیید
جداگانه پرهیز کند.

### نمونه نام‌گذاری فایل‌ها

``` text
2026_10_01_000001_create_profiles_table.php
2026_10_01_000002_create_companies_table.php
2026_10_01_000003_create_products_table.php
2026_10_01_000004_create_projects_table.php
2026_10_01_000005_create_experiences_table.php
2026_10_01_000006_create_education_table.php
2026_10_01_000007_create_skills_table.php
2026_10_01_000008_create_technologies_table.php
2026_10_01_000009_create_notes_table.php
```

نام و timestamp نهایی باید توسط Artisan تولید شود؛ نمونه‌ها صرفاً برای
نشان دادن ترتیب هستند.

------------------------------------------------------------------------

## 9. چندزبانه‌سازی: تصمیمی که باید قبل از Migration نهایی شود

پیشنهاد فعلی: **محتوا را از ابتدا برای چندزبانه‌سازی قابل توسعه طراحی
کنیم، اما تمام موجودیت‌ها را از روز اول به سیستم ترجمه‌ی پیچیده مجهز
نکنیم.**

دو راه وجود دارد:

### گزینه A --- ستون‌های ترجمه در جدول‌های مجزا

مثلاً `note_translations` با `note_id`, `locale`, `title`, `slug`,
`excerpt`, `body`. مزیت: مدل رابطه‌ای روشن و امکان تفاوت واقعی محتوا بین
زبان‌ها. هزینه: query و پنل مدیریت پیچیده‌تر.

### گزینه B --- فقط فارسی در MVP، افزودن ترجمه بعداً

متن و slug فعلاً در جدول اصلی نگهداری شوند، ولی کد از ابتدا locale-aware
نوشته شود. هنگام اضافه شدن انگلیسی، Migration ترجمه‌ها و مسیرها طراحی
شود. مزیت: شروع ساده‌تر. ریسک: اگر slugهای چندزبانه از ابتدا نیاز قطعی
باشند، مهاجرت بعدی کار اضافه دارد.

**پیشنهاد اجرایی:** اگر نسخه‌ی انگلیسی در نسخه‌ی اول منتشر نمی‌شود، گزینه B
را انتخاب کنیم و پیش از انتشار انگلیسی، schema ترجمه را اضافه کنیم. از
ایجاد ستون‌های `title_fa`, `title_en`, `body_fa`, `body_en` پرهیز شود؛
این روش با اضافه شدن زبان سوم شکننده می‌شود.

Slug عمومی باید سیاست یکتا و redirect برای تغییر slug داشته باشد. اگر
ترجمه‌ها slug مستقل داشته باشند، unique constraint باید روی
`(locale, slug)` در جدول ترجمه اعمال شود.

------------------------------------------------------------------------

## 10. تصمیم‌های طراحی باز برای تأیید

پیش از نوشتن Migrationهای اجرایی، این موارد را قطعی کنیم:

1.  **Slug و ترجمه:** آیا نسخه انگلیسی در انتشار اولیه لازم است؟ اگر
    خیر، فعلاً فارسی و معماری locale-aware کافی است.
2.  **رسانه:** آیا اتصال polymorphic از طریق `media_attachments` برای
    تمام موجودیت‌ها پذیرفته است؟
3.  **Timeline:** آیا هر رویداد فقط به یک موجودیت اصلی متصل می‌شود؟
    پیشنهاد MVP: بله.
4.  **Soft Delete:** برای محتواهای اصلی لازم است یا آرشیو با `status`
    کافی است؟ پیشنهاد: برای MVP از `status=archived` استفاده شود و soft
    delete فقط برای مواردی که بازیابی واقعاً لازم است.
5.  **AI Provider:** آیا در نسخه اول قابلیت AI اجرایی می‌خواهیم یا فقط
    جایگاه معماری؟ پیشنهاد: معماری مشخص، Migration و UI در فاز بعد از
    نیاز واقعی.
6.  **Author:** آیا فقط یک مدیر/نویسنده هست یا یادداشت‌ها باید به کاربر
    مشخص منتسب شوند؟ پیشنهاد: در صورت چند نویسنده، `author_id` nullable
    به `users` اضافه شود؛ در غیر این صورت حذف شود.
7.  **Company/Product/Project:** تأیید می‌شود که Product--Project
    چندبه‌چند باشد؟ پیشنهاد: بله، چون یک پروژه می‌تواند در چند محصول
    استفاده شود.

------------------------------------------------------------------------

## 11. امنیت، حذف و یکپارچگی داده

-   Foreign Key برای روابط اصلی اجباری است؛ رابطه‌های polymorphic
    به‌تنهایی نمی‌توانند Foreign Key به جدول‌های هدف داشته باشند.
-   حذف Company/Product/Project نباید آبشاری به موجودیت‌های محتوایی دیگر
    انجام شود.
-   حذف رکورد از pivotها می‌تواند cascade شود، چون pivot مالک محتوای اصلی
    نیست.
-   `CurrentFocus` در صورت حذف هدف مرتبط باید به‌صورت `nullOnDelete` یا
    از طریق قاعده‌ی آرشیو مدیریت شود.
-   برای رسانه‌ها ابتدا attachmentها پاک/جدا شوند؛ حذف فایل فیزیکی فقط پس
    از بررسی اینکه هیچ اتصال دیگری از آن استفاده نمی‌کند انجام شود.
-   روی `slug`, `status`, `published_at`, `sort_order`, و Foreign
    Keyهایی که در queryها استفاده می‌شوند index مناسب تعریف شود؛ index
    اضافی بدون query واقعی ایجاد نشود.
-   داده‌های خصوصی پروفایل، اطلاعات تماس غیرعمومی و API keyها نباید در
    صفحات عمومی serialize شوند.
-   تنظیمات حساس در `settings` عمومی قرار نگیرند.
-   Seederها باید idempotent باشند: اجرای دوباره نباید داده‌ی تکراری
    تولید کند یا داده‌ی مدیر را ناخواسته بازنویسی کند.
-   برای محیط Production از `migrate:fresh` استفاده نشود. در پروژه‌ی جدید
    توسعه می‌توان دیتابیس آزمایشی را از نو ساخت، اما فرمان‌های مخرب باید
    محدود به محیط محلی و دیتابیس غیرحساس باشند.

------------------------------------------------------------------------

## 12. مدل‌ها و پوشه‌بندی پیشنهادی Laravel

برای شروع از ساختار استاندارد Laravel استفاده می‌کنیم و فقط وقتی پیچیدگی
واقعی پدید آمد، لایه‌بندی بیشتری اضافه می‌کنیم:

``` text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   └── Public/
│   ├── Requests/
│   │   └── Admin/
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Profile.php
│   ├── Company.php
│   ├── Product.php
│   ├── Project.php
│   ├── Experience.php
│   ├── Education.php
│   ├── Skill.php
│   ├── Technology.php
│   ├── Note.php
│   ├── Category.php
│   ├── Tag.php
│   ├── TimelineEvent.php
│   ├── CurrentFocus.php
│   ├── Media.php
│   ├── MediaAttachment.php
│   ├── SeoMetadata.php
│   ├── ContactChannel.php
│   ├── Module.php
│   ├── HomepageSection.php
│   ├── Setting.php
│   └── AiProvider.php
├── Policies/
├── Services/
│   ├── Media/
│   ├── Seo/
│   └── AI/
└── Support/
```

این یک نقشه‌ی اولیه است، نه الزام به ساخت همه‌ی فایل‌ها از روز اول. مدل‌ها و
ماژول‌های خارج از MVP باید مرحله‌ای اضافه شوند.

------------------------------------------------------------------------

## 13. تست‌های ضروری قبل از پذیرش Schema

1.  اجرای تمام Migrationها روی دیتابیس آزمایشی خالی.
2.  اجرای `migrate:rollback` برای batchهای آزمایشی و اطمینان از `down()`
    صحیح.
3.  تست یکتایی slug.
4.  تست جلوگیری از رکورد تکراری در pivotها.
5.  تست سیاست حذف شرکت و هدف‌های مرتبط.
6.  تست انتشار: محتوای draft، private یا آینده‌زمان‌بندی‌شده نباید در
    queryهای عمومی نمایش داده شود.
7.  تست `CurrentFocus` که بیش از یک target نداشته باشد.
8.  تست رمزگذاری و عدم بازگشت API key در response.
9.  تست محدودیت‌های آپلود رسانه و اعتبارسنجی MIME/size.
10. تست Seederها با اجرای تکراری.
11. تست indexها و queryهای مهم با داده‌ی نمونه.
12. بررسی سازگاری Migrationها با نسخه‌ی واقعی MySQL در محیط استقرار.

------------------------------------------------------------------------

## 14. پیشنهاد Scope برای MVP

### ضروری در نسخه اول

-   `users`
-   `profiles`
-   `companies`
-   `products`
-   `projects`
-   `experiences`
-   `education`
-   `skills`
-   `technologies`
-   `notes`
-   `categories`
-   `tags`
-   pivotهای موردنیاز
-   `current_focuses`
-   `timeline_events`
-   `contact_channels`
-   `media` و `media_attachments`
-   `seo_metadata`
-   `modules`
-   `homepage_sections`
-   `settings`

### قابل تعویق

-   `ai_providers` و UI تنظیمات AI تا زمانی که قابلیت AI واقعاً وارد
    برنامه‌ی اجرایی شود.
-   سیستم کامل ترجمه تا زمانی که محتوای انگلیسی در نسخه اول نیاز نباشد.
-   نقش‌ها و مجوزهای چندسطحی تا وقتی بیش از یک مدیر یا نویسنده نداریم.
-   جست‌وجوی مستقل مانند Elasticsearch؛ ابتدا MySQL.
-   revision history کامل؛ در صورت نیاز واقعی به مرحله بعد اضافه شود.

------------------------------------------------------------------------

## 15. خروجی مرحله و گام بعدی

خروجی این مرحله، نقشه‌ی مدل‌ها، فیلدها، روابط و ترتیب Migrationهاست. گام
بعدی باید **تبدیل همین Blueprint به Migrationهای اجرایی و مدل‌های
Eloquent** باشد، اما ابتدا تصمیم‌های بخش 10 را نهایی کنیم.

پس از تأیید: 1. ساخت پروژه‌ی Laravel جدید در مسیر جدا از سایت فعلی. 2.
تنظیم `.env` محلی بدون استفاده از اطلاعات محرمانه در مخزن. 3. ساخت
Migrationها به ترتیب وابستگی. 4. ساخت مدل‌ها و رابطه‌های Eloquent. 5.
نوشتن Factory و Seederهای idempotent. 6. اجرای تست روی دیتابیس توسعه. 7.
سپس ساخت CMS و صفحات عمومی.

**هشدار نهایی:** این سند یک Blueprint معماری است، نه فرمان برای اجرای
فوری روی Production. هیچ دیتابیس یا فایل فعلی نباید پیش از ایجاد نسخه‌ی
جدید، تست کامل و تأیید صریح جایگزینی حذف شود.
