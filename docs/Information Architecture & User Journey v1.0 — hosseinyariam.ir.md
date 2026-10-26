# Information Architecture & User Journey v1.0 — hosseinyariam.ir

**Version:** 1.0  
**Date:** 2026-09-28  
**Status:** Discovery-derived / Draft  
**Language:** Persian-first, English-ready

## 1. هدف

`hosseinyariam.ir` یک **Professional Identity Hub / Living Professional Profile** است؛ نه صرفاً رزومه، Portfolio، Blog یا سایت یک محصول.

هدف اصلی:

> وقتی کسی نام «حسین یاریم» را جستجو می‌کند، بفهمد حسین کیست، چه نوع مهندس و سازنده‌ای است، چه چیزهایی ساخته و چگونه به مسائل نگاه می‌کند.

## 2. تجربه مطلوب

کاربر باید بتواند به این برداشت برسد:

> «الان دقیقاً می‌فهمم حسین کیه، چه جور آدم و مهندسیه، چه چیزهایی ساخته و چطور به مسائل نگاه می‌کنه.»

و:

> «حسین آدمیه که واقعاً محصول می‌سازه و مسئله رو جدی می‌گیره.»

مسیر طبیعی شناخت:

```text
من کی هستم؟
↓
چه کار می‌کنم؟
↓
چه مسئله‌هایی را دنبال می‌کنم؟
↓
چه چیزهایی ساخته‌ام؟
↓
چطور ساخته‌ام؟
↓
چه چیزهایی یاد گرفته‌ام؟
↓
الان روی چه چیزی کار می‌کنم؟
↓
مسیر حرفه‌ای من چگونه شکل گرفته؟
↓
در صورت نیاز: ارتباط / ادامه کشف
```

این مسیر اجباری نیست؛ کاربر می‌تواند از هر نقطه وارد شود.

## 3. معماری سطح بالا

```text
hosseinyariam.ir
├── Home
├── Biography
├── Projects
├── Products
├── Companies
├── Notes
├── Journey / Timeline
├── Current Focus
└── Contact / Connect
```

همه این‌ها الزاماً در Navigation اصلی نیستند.

## 4. Homepage — Living Profile

Homepage باید Dynamic باشد و وضعیت فعلی حرفه‌ای حسین را نشان دهد.

ساختار محتوایی پیشنهادی:

```text
Home
├── Identity / Hero
├── Current Focus
│   ├── Currently Building
│   ├── Currently Learning
│   └── Exploring
├── Selected Things I've Built
├── How I Work / Think
├── Professional Journey
├── Recent Activity
└── Discovery / Connect
```

اطلاعاتی مانند Current Focus، Recent Notes، Recent Projects و Timeline از CMS تغذیه می‌شوند.

## 5. Biography

Route:

```text
/biography/
```

Biography یک **Professional Profile / Professional Case File** کامل است، نه Resume ساده.

شامل:

- Introduction
- Professional Story
- Education
- Experiences
- Skills
- Technologies
- Achievements
- Certificates
- Courses / Learning
- Professional Interests
- Future Direction
- Downloadable PDF

Companies، Projects، Products، Collaborations و Social Profiles موجودیت‌های جدا هستند و در صورت نیاز از Biography به آن‌ها لینک می‌شوند.

## 6. Projects

Route:

```text
/projects/
/projects/{project}
```

Project یک سابقه کاری/ساختنی است و می‌تواند شخصی، دانشگاهی، Freelance، بخشی از Product یا یک تجربه متوقف/ناموفق باشد.

ساختار:

```text
Project
├── Overview
├── Problem
├── Context
├── Role
├── Contribution
├── Technology
├── Timeline
├── Status
├── Outcome
├── Lessons Learned
└── Related Content
```

Statusهای پیشنهادی:

- Building
- Active
- Completed
- Paused
- Archived
- Failed / Learned

## 7. Products

Route:

```text
/products/
/products/{product}
```

Product با Project یکی نیست.

یک Product می‌تواند چند Project، Note و Timeline Event مرتبط داشته باشد.

```text
Product
├── Product Story
├── Problem
├── Solution
├── Current State
├── Related Projects
├── Related Notes
├── Timeline
└── External / Product URL
```

## 8. Companies

Route:

```text
/companies/
/companies/{company}
```

Company موجودیت مستقل است و با Product یا Project یکی نمی‌شود.

```text
Company
├── Description
├── Role / Relationship
├── Timeline
├── Products
├── Projects
└── Related Notes
```

## 9. Notes / Articles

Route:

```text
/notes/
/notes/{note}
```

Notes یک بخش جدی ولی غیرالزامی است:

> چیزهایی که حسین در مسیر ساختن، حل مسئله و یادگیری ارزش ثبت کردن می‌داند.

حوزه‌های اولیه:

- Building Products
- Problem Solving
- Software Engineering
- AI
- Entrepreneurship
- Lessons Learned
- Personal Growth

Notes می‌تواند شامل Article، Note، Experience، Lesson Learned، Technical Write-up و Product-building Story باشد.

Notes معمولاً تاریخ انتشار دارند؛ داده‌های ساختاریافته مثل Skill یا Education الزاماً تاریخ انتشار ندارند.

## 10. Journey / Timeline

Route پیشنهادی:

```text
/journey/
```

Timeline باید **Curated** باشد: سیستم می‌تواند رویدادهای زیادی ذخیره کند، اما فقط نقاط مهم انتخاب‌شده Public شوند.

نمونه:

```text
2021 → Web / Freelance
2024 → Hamyar Camping
2026 → Hamsaz → Vinora → Product Building
```

Timeline می‌تواند به Project، Product، Company، Experience، Education، Learning و Note متصل شود.

## 11. Current Focus

Current Focus یک Module مستقل برای وضعیت فعلی است:

```text
Currently Building
Currently Learning
Exploring
Current Goal
```

در فاز اول عمدتاً در Homepage نمایش داده می‌شود و بعداً در صورت نیاز صفحه مستقل خواهد داشت.

## 12. Contact / Connect

هدف:

> ایجاد یک نقطه واضح برای ارتباط با حسین.

می‌تواند شامل Email، Contact Form، LinkedIn، GitHub و سایر شبکه‌های مورد تأیید باشد.

Social Profiles موجودیت اصلی سایت نیستند و هر شبکه محتوای مخصوص خودش را دارد. سایت مرکز هویت حرفه‌ای و مرجع اصلی اطلاعات است.

## 13. Navigation

اصل:

```text
CMS Complexity ≠ Navigation Complexity
```

ممکن است CMS بخش‌های زیادی داشته باشد، ولی Navigation خلوت بماند.

نمونه احتمالی:

```text
Biography
Work / Projects
Products
Notes
Journey
Contact
```

Skills، Education، Certificates، Achievements، Learning، Experiences و Current Focus الزاماً Navigation مستقل نیستند و می‌توانند در صفحات دیگر نمایش داده شوند.

## 14. Modular Architecture

Moduleها قابل فعال/غیرفعال شدن هستند:

```text
Biography       ON
Projects        ON
Products        ON
Companies       ON
Notes           ON
Timeline        ON
Current Focus   ON
Skills          ON
Education       ON
Achievements    ON
Certificates    ON
Learning        ON
```

خاموش کردن یک Module نباید نیازمند حذف کد یا بازطراحی کل سایت باشد.

CMS می‌تواند غنی باشد، اما Public Website باید خلوت و انتخاب‌شده باقی بماند.

## 15. ارتباط موجودیت‌ها

```text
Hossein
├── Project
├── Product
├── Company
├── Notes
├── Timeline
├── Experience
├── Skills
└── Learning
```

روابط نمونه:

```text
Company → Products
Product → Projects
Product → Notes
Project → Notes
Timeline Event → Project/Product/Company/Experience
```

یک صفحه باید بتواند Related Content داشته باشد تا کاربر مسیر کشف بعدی داشته باشد.

## 16. Discovery Loop

```text
Current Page
↓
Related Content
↓
New Question
↓
Next Page
↓
More Context
↓
Deeper Understanding
```

مثال:

```text
Vinora
↓
Problem
↓
Project
↓
Lessons Learned
↓
Note
↓
Journey
```

هدف، کشف مفید است؛ نه افزایش مصنوعی زمان ماندن کاربر.

## 17. User Journeys

### A — جستجوی نام حسین

```text
Google → Homepage → Biography → Projects → Current Focus → Contact/Social
```

### B — علاقه‌مند به کارهای ساخته‌شده

```text
External → Homepage → Things I've Built → Projects → Project Detail
→ Related Product → Related Notes → Journey
```

### C — علاقه‌مند به Vinora

```text
External → Product Page → Problem → Solution → Projects
→ Lessons/Notes → Current State → Product/Contact
```

### D — علاقه‌مند به طرز فکر حسین

```text
External → Homepage → How I Think → Notes → Article
→ Related Project → Experience → Biography
```

### E — فردی که برای همکاری آمده

```text
Homepage → Biography → Experience → Projects/Products
→ Current Focus → Contact
```

### F — بازدیدکننده‌ای که فقط می‌خواهد حسین را بشناسد

```text
Homepage → Identity → Current Focus → Selected Work
→ Biography → Exit / Follow
```

## 18. Search

با رشد محتوا، Search باید بتواند در انواع محتوا جستجو کند:

- Projects
- Products
- Companies
- Notes
- Timeline
- Skills
- Experiences

فاز اول می‌تواند ساده باشد و بعداً ارتقا یابد.

## 19. Public / Private

مدل پیشنهادی:

```text
Draft
Private
Public
Archived
```

و در صورت نیاز:

```text
Featured
```

CMS غنی‌تر از Public Website است.

## 20. Multilingual

فعلاً:

```text
Persian
```

اما معماری از ابتدا:

```text
Persian-first, multilingual-ready
```

یک Entity باید در آینده قابلیت چند Locale داشته باشد:

```text
Product
├── fa
└── en
```

English در فاز اول فعال نیست.

## 21. SEO

هر Entity عمومی مهم باید قابلیت:

- Title
- Meta Description
- Canonical
- Slug
- Open Graph
- Structured Data where applicable
- Sitemap inclusion

داشته باشد.

AI می‌تواند metadata اولیه پیشنهاد دهد، ولی کنترل نهایی با Admin است.

## 22. Automation

اصل:

> **Automate the repetitive, control the meaningful.**

سیستم می‌تواند Slug، SEO metadata، Sitemap، Breadcrumb، Open Graph، Related Content، Timeline relations و برخی نمایش‌های Dynamic را خودکار کند.

اما تصمیم‌های معنایی مانند Public/Private، Featured، Priority و Homepage visibility قابل کنترل هستند.

## 23. AI Layer

CMS باید یک AI Service مرکزی داشته باشد:

```text
AI Configuration
├── Provider
├── Model
├── Base URL
├── API Key
└── Default Parameters

Modules
↓
Internal AI Service
↓
Provider / Model
```

کاربردهای احتمالی:

- پیشنهاد عنوان
- خلاصه‌سازی
- Meta Description
- SEO metadata
- Tags
- Related Content
- Draft assistance
- تحلیل محتوای پروژه
- Automationهای آینده

API Key فقط در Backend نگهداری می‌شود و به Browser ارسال نمی‌شود.

## 24. Public Information Architecture

```text
/
├── biography/
├── projects/
│   └── {project}
├── products/
│   └── {product}
├── companies/
│   └── {company}
├── notes/
│   └── {note}
├── journey/
└── contact/
```

Current Focus در فاز اول Module Homepage است.

## 25. Evolution

### Phase 1

```text
Hossein
├── Biography
├── Projects
├── Vinora
├── Notes
├── Journey
└── Contact
```

### Phase 2

```text
Hossein
├── Biography
├── Projects
├── Products
├── Companies
├── Notes
├── Journey
└── Current Focus
```

### Phase 3

```text
Hossein
├── Professional Identity
├── Companies
│   ├── Product A
│   ├── Product B
│   └── Product C
├── Projects
├── Notes
├── Journey
└── Network / Connect
```

رشد نباید نیازمند بازطراحی بنیادی CMS باشد.

## 26. اصول قطعی

1. Identity First
2. Problem First
3. Evidence Over Claims
4. Project ≠ Product ≠ Company
5. Biography ≠ Resume
6. Homepage = Living Profile
7. Current Focus = Dynamic
8. Timeline = Curated
9. Notes = Experience-driven
10. CMS can be richer than Public Website
11. Modules can be enabled/disabled
12. Related Content creates discovery
13. Persian-first, English-ready
14. Automate repetitive work
15. Human controls meaningful decisions
16. Optimize for useful discovery, not artificial time-on-site
17. Public pages remain clean despite rich backend data
18. Architecture supports future Products and Companies

## 27. مواردی که به مرحله بعد موکول شدند

- Database Schema
- Eloquent Models
- Relationships
- Admin UI
- Permissions
- Laravel folder structure
- API architecture
- AI implementation
- Caching
- Queue architecture
- Search implementation
- Exact Navigation labels
- Homepage UI
- Design system
- Component library
- Animation
- Performance implementation

## 28. مرحله بعد

### Software Architecture & CMS Architecture

```text
Information Architecture
↓
Domain Model
↓
Entities
↓
Relationships
↓
Content Architecture
↓
Module System
↓
Admin CMS
↓
AI Service Layer
↓
Laravel Architecture
↓
Database
↓
Caching / Performance
↓
Security
↓
Implementation Plan
```

اصل:

> ابتدا مدل مفهومی سیستم مشخص می‌شود؛ سپس Database از روی آن طراحی می‌شود، نه برعکس.
