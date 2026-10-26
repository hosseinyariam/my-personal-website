# Software Architecture & CMS Architecture v1.0 — hosseinyariam.ir

**Version:** 1.0  
**Date:** 2026-09-28  
**Status:** Proposed Architecture  
**Based on:** Information Architecture & User Journey v1.0  
**Framework:** Laravel 12.x  
**Primary Language:** Persian  
**Future Language:** English  
**Architecture Goal:** Modular, Content-driven, extensible, maintainable

---

# 1. Architectural Vision

`hosseinyariam.ir` باید از یک وب‌سایت شخصی به یک **Content-driven Professional Identity Platform** تبدیل شود.

دو بخش اصلی:

```text
                 HOSSEIN YARIAM PLATFORM
                         │
             ┌───────────┴───────────┐
             │                       │
        Public Website           Admin CMS
             │                       │
      User Experience          Content Management
             │                       │
             └───────────┬───────────┘
                         │
                  Domain / Content
                         │
             ┌───────────┼───────────┐
             │           │           │
           Data        Modules       AI
```

اصل کلیدی:

> Public Website باید ساده و سریع باشد؛ CMS باید غنی، ماژولار و قابل توسعه باشد.

---

# 2. Architectural Principles

## 2.1 Domain First

ابتدا مفاهیم واقعی دامنه مشخص می‌شوند، سپس Database طراحی می‌شود.

## 2.2 Modular by Default

هر قابلیت بزرگ باید قابلیت فعال/غیرفعال شدن داشته باشد.

## 2.3 Entity Separation

```text
Project ≠ Product ≠ Company
```

هرکدام مدل دامنه مستقل دارند.

## 2.4 Content-driven

صفحات عمومی باید تا حد امکان از داده‌های CMS تولید شوند.

## 2.5 Dynamic Homepage

Homepage باید از Current Focus، Featured Content، Recent Content و Timeline تغذیه شود.

## 2.6 Automate Repetitive Work

کارهای تکراری خودکار شوند، اما تصمیم‌های معنایی در اختیار Admin بمانند.

## 2.7 Persian-first / English-ready

مدل داده باید از ابتدا قابلیت Localization داشته باشد.

## 2.8 Secure by Default

اطلاعات Private و Secret هرگز نباید وارد Public Response شوند.

## 2.9 SEO by Architecture

SEO نباید یک افزونه/مرحله آخر باشد؛ باید در مدل Content و Rendering لحاظ شود.

## 2.10 Evidence over Claims

ساختار Project/Product/Experience باید امکان نمایش شواهد و خروجی واقعی را فراهم کند.

---

# 3. Architectural Layers

معماری پیشنهادی:

```text
┌─────────────────────────────────────────┐
│ Presentation                            │
│ Blade / Components / Tailwind / JS      │
├─────────────────────────────────────────┤
│ Application                             │
│ Use Cases / Actions / Services          │
├─────────────────────────────────────────┤
│ Domain                                  │
│ Entities / Rules / Relationships        │
├─────────────────────────────────────────┤
│ Infrastructure                          │
│ Eloquent / Cache / Storage / AI / SEO   │
├─────────────────────────────────────────┤
│ Persistence                             │
│ MySQL                                   │
└─────────────────────────────────────────┘
```

Laravel می‌تواند این لایه‌ها را با ساختار ساده و قابل فهم پیاده کند؛ هدف ایجاد Architecture بیش از حد Enterprise نیست.

---

# 4. Core Domain Entities

موجودیت‌های اصلی:

```text
User
Profile
Experience
Education
Skill
Technology
Achievement
Certificate
Course
Project
Product
Company
Note
TimelineEvent
CurrentFocus
ContactChannel
Module
Setting
Media
Tag
Category
```

برخی از این‌ها در فاز اول می‌توانند ساده باشند و بعداً گسترش پیدا کنند.

---

# 5. Identity Domain

## Profile

اطلاعات اصلی هویت حرفه‌ای حسین:

- Name
- Professional Title
- Short Bio
- Long Bio
- Profile Image
- Positioning Statement
- Core Values
- Philosophy
- Contact Summary

Profile با User/Admin Account یکی نیست.

---

# 6. Experience

برای سابقه حرفه‌ای:

```text
Experience
├── Title
├── Organization
├── Description
├── Start Date
├── End Date
├── Current
├── Location
├── Responsibilities
├── Achievements
└── Visibility
```

Experience می‌تواند به Project، Company و Skills مرتبط شود.

---

# 7. Education

```text
Education
├── Institution
├── Degree
├── Field
├── Start Date
├── End Date
├── Description
└── Visibility
```

---

# 8. Skill / Technology

Skills و Technologies از Experience و Project قابل استفاده هستند.

```text
Skill
├── Name
├── Category
├── Level (optional)
├── Description
└── Visibility
```

```text
Technology
├── Name
├── Category
├── Icon
└── Visibility
```

سطح مهارت نباید الزاماً با امتیاز عددی نمایش داده شود؛ برای جلوگیری از ادعاهای غیرقابل سنجش، نمایش کیفی یا صرفاً تجربه مرتبط ترجیح داده می‌شود.

---

# 9. Project Domain

```text
Project
├── Title
├── Slug
├── Summary
├── Description
├── Problem
├── Context
├── Role
├── Contribution
├── Outcome
├── Lessons Learned
├── Status
├── Start Date
├── End Date
├── Featured
├── Public
├── SEO
└── Media
```

Project می‌تواند با موارد زیر مرتبط باشد:

```text
Project
├── Product
├── Company
├── Skills
├── Technologies
├── Notes
├── Timeline Events
└── Experiences
```

---

# 10. Product Domain

```text
Product
├── Name
├── Slug
├── Summary
├── Description
├── Problem
├── Solution
├── Current State
├── Status
├── Launch Date
├── Featured
├── Public
├── External URL
├── SEO
└── Media
```

ارتباط‌ها:

```text
Product
├── Company
├── Projects
├── Notes
├── Timeline Events
└── Technologies
```

---

# 11. Company Domain

```text
Company
├── Name
├── Slug
├── Description
├── Website
├── Relationship
├── Start Date
├── End Date
├── Current
├── Public
├── SEO
└── Media
```

ارتباط:

```text
Company
├── Products
├── Projects
├── Experiences
└── Timeline Events
```

---

# 12. Notes Domain

Notes یک Content Entity است.

```text
Note
├── Title
├── Slug
├── Excerpt
├── Body
├── Type
├── Category
├── Tags
├── Status
├── Published At
├── Updated At
├── Featured
├── Public
├── SEO
└── Media
```

Type می‌تواند:

```text
Article
Note
Experience
Lesson
Technical Write-up
Product Story
```

باشد.

---

# 13. Timeline Domain

```text
TimelineEvent
├── Title
├── Description
├── Date
├── End Date (optional)
├── Type
├── Featured
├── Public
└── Related Entities
```

Timeline Event می‌تواند به:

- Project
- Product
- Company
- Experience
- Education
- Note
- Learning

متصل شود.

برای روابط چندنوعی، Polymorphic Relation در صورت نیاز استفاده می‌شود؛ اما فقط جایی که واقعاً ارزش معماری داشته باشد.

---

# 14. Current Focus Domain

Current Focus وضعیت فعلی حرفه‌ای را نگه می‌دارد.

```text
CurrentFocus
├── Type
├── Title
├── Description
├── Status
├── Priority
├── Start Date
├── End Date
├── Related Entity
├── Public
└── Featured
```

Type:

```text
Building
Learning
Exploring
Goal
```

می‌تواند باشد.

---

# 15. Contact Channel

```text
ContactChannel
├── Type
├── Label
├── URL / Value
├── Icon
├── Public
└── Sort Order
```

مثلاً:

```text
Email
LinkedIn
GitHub
Instagram
Telegram
```

اما هر شبکه محتوای مستقل خودش را دارد؛ این Entity فقط راه ارتباط/لینک را مدیریت می‌کند.

---

# 16. Module System

Module یکی از مهم‌ترین بخش‌های Architecture است.

```text
Module
├── Key
├── Name
├── Description
├── Enabled
├── Configuration
└── Sort Order
```

مثال:

```text
biography
projects
products
companies
notes
timeline
current_focus
skills
education
achievements
certificates
learning
```

Module System باید از Hard-code کردن Visibility در Viewها جلوگیری کند.

---

# 17. Module vs Content

این دو مفهوم نباید یکی شوند.

```text
Module
    =
قابلیت / بخش سیستم

Content
    =
داده‌ای که داخل آن قابلیت نمایش داده می‌شود
```

مثلاً:

```text
Notes Module
       ↓
Note entities
       ↓
Published notes
```

و:

```text
Projects Module
       ↓
Project entities
       ↓
Public projects
```

---

# 18. Homepage Composition

Homepage باید ترکیبی از Moduleها باشد.

مدل پیشنهادی:

```text
Homepage
├── Hero
├── Current Focus
├── Featured Projects
├── Featured Products
├── Selected Notes
├── Timeline Preview
├── Biography Preview
└── Connect
```

هر Section باید بتواند:

- Enabled/Disabled
- Sort Order
- Visibility
- Limit
- Selection Mode

داشته باشد.

---

# 19. Manual vs Automatic Selection

برای بخش‌هایی مثل Featured Projects سه حالت مفید است:

```text
Selection Mode
├── Automatic
├── Manual
└── Hybrid
```

مثلاً:

Automatic:

```text
آخرین Projects عمومی
```

Manual:

```text
Projects انتخاب‌شده توسط Admin
```

Hybrid:

```text
Pinned content
+
Automatic fallback
```

این مدل نیاز به تغییر کد برای هر Homepage update را کاهش می‌دهد.

---

# 20. Visibility Model

پیشنهاد:

```text
Visibility
├── Draft
├── Private
├── Public
└── Archived
```

و Flagهای مکمل:

```text
Featured
Homepage Visible
Searchable
Timeline Visible
```

این‌ها نباید همه یک مفهوم واحد باشند.

---

# 21. Content Relationships

برای Related Content دو نوع رابطه پیشنهاد می‌شود.

## Explicit

Admin رابطه را مشخص می‌کند.

مثلاً:

```text
Vinora
→ Related Note #14
```

## Automatic

سیستم بر اساس:

- Tags
- Categories
- Entity relations
- Shared Technologies
- Shared Products
- Content similarity

پیشنهاد می‌دهد.

در فاز اول Explicit + قواعد ساده Automatic کافی است.

---

# 22. Taxonomy

برای جلوگیری از Hard-code:

```text
Category
Tag
```

به‌صورت داده مدیریت شوند.

مثلاً:

```text
Categories
├── AI
├── Product Building
├── Software Engineering
├── Entrepreneurship
└── Personal Growth
```

Tagها می‌توانند آزادتر باشند.

---

# 23. Media Architecture

Media باید موجودیت مستقل داشته باشد.

```text
Media
├── File
├── Type
├── Alt
├── Caption
├── Title
├── Disk
├── Size
├── Metadata
└── Visibility
```

در آینده امکان:

- Images
- PDFs
- Project screenshots
- Product logos
- Documents

وجود دارد.

---

# 24. SEO Architecture

بهتر است SEO metadata در یک ساختار مشترک مدیریت شود.

مفهوم:

```text
SeoMetadata
├── Title
├── Description
├── Canonical
├── Robots
├── OG Title
├── OG Description
└── OG Image
```

Entityهای Public مهم بتوانند SEO داشته باشند.

---

# 25. Localization Architecture

مدل پیشنهادی:

```text
Entity
   ↓
Translations
   ├── fa
   └── en
```

در فاز اول فقط `fa` فعال است.

نباید برای English یک Database جدا ساخته شود.

---

# 26. Admin CMS

CMS باید از نظر مفهومی به بخش‌های زیر تقسیم شود:

```text
Dashboard
│
├── Content
│   ├── Projects
│   ├── Products
│   ├── Companies
│   ├── Notes
│   ├── Experiences
│   ├── Education
│   ├── Skills
│   ├── Certificates
│   └── Achievements
│
├── Journey
│   ├── Timeline
│   └── Current Focus
│
├── Identity
│   ├── Profile
│   └── Biography
│
├── Presentation
│   ├── Homepage
│   ├── Modules
│   └── Navigation
│
├── Media
│
├── SEO
│
├── Settings
│
└── AI
```

این ساختار هنوز UI نهایی نیست.

---

# 27. Admin Dashboard

Dashboard باید به‌جای صرفاً نمایش تعداد رکوردها، وضعیت سایت را نشان دهد.

مثلاً:

```text
Current Focus
Recent Content
Drafts
Scheduled Content
Projects in Progress
Missing SEO
Unoptimized Media
AI usage / status
```

---

# 28. AI Architecture

AI نباید در هر ماژول به‌صورت جداگانه پیاده شود.

مدل:

```text
Admin / Automation
       ↓
AI Application Service
       ↓
AI Provider Adapter
       ↓
OpenAI / Compatible API / Other Provider
```

Provider Adapter باعث می‌شود Provider قابل تعویض باشد.

تنظیمات:

```text
Provider
Model
Base URL
API Key
Temperature
Max Tokens
Enabled
```

مقادیر حساس باید رمزنگاری‌شده ذخیره شوند.

---

# 29. AI Safety / Cost Controls

AI Layer باید از ابتدا امکان کنترل داشته باشد:

```text
Enabled / Disabled
Allowed Modules
Request Logging
Usage Tracking
Timeout
Rate Limit
```

AI نباید بدون کنترل در هر Save عملیات خارجی انجام دهد.

برای Automationهای سنگین، Queue/Job می‌تواند در آینده استفاده شود.

---

# 30. Application Services

برای منطق قابل استفاده مجدد، Service/Actionهای مشخص:

```text
ContentPublishingService
RelatedContentService
HomepageCompositionService
SeoService
TimelineService
CurrentFocusService
AiService
MediaService
SearchService
```

هدف جلوگیری از قرار گرفتن منطق پیچیده داخل Controllerها و Bladeهاست.

---

# 31. Routing

Public routes باید resource-oriented باشند:

```text
/
 /biography
 /projects
 /projects/{slug}
 /products
 /products/{slug}
 /companies
 /companies/{slug}
 /notes
 /notes/{slug}
 /journey
 /contact
```

Admin:

```text
/admin
/admin/projects
/admin/products
/admin/companies
/admin/notes
...
```

Routing نباید به نام Controllerها وابسته باشد.

---

# 32. Database Direction

Database باید Relational باشد.

MySQL مناسب است.

اصل:

> ساختار Database از Domain Model استخراج می‌شود.

جدول‌های اصلی احتمالی:

```text
users
profile
experiences
education
skills
technologies
projects
products
companies
notes
timeline_events
current_focuses
contact_channels
modules
categories
tags
media
seo_metadata
settings
```

Pivot tableها برای روابط Many-to-Many استفاده می‌شوند.

---

# 33. Polymorphic Relations

Polymorphic relations فقط برای مواردی که واقعاً چند نوع Entity می‌توانند مقصد رابطه باشند استفاده شوند.

موارد مناسب:

```text
TimelineEvent → related entity
Media → attachable entity
SEO → seoable entity
```

اما نباید تمام سیستم به یک Generic Polymorphic Content تبدیل شود.

اصل:

> Explicit domain relationships where semantics matter; polymorphism where reuse clearly helps.

---

# 34. Search Architecture

فاز اول:

```text
Database-backed search
```

با رشد محتوا:

```text
Dedicated Search Engine
```

قابل اضافه شدن باشد.

Search Service باید Interface داشته باشد تا Implementation قابل تعویض باشد.

---

# 35. Cache Architecture

Caching باید هدفمند باشد.

موارد مناسب:

- Homepage composition
- Public navigation
- Public settings
- Popular/featured content
- Related content

بعد از Publish/Update باید Cache invalidation انجام شود.

در محیط فعلی Shared Hosting، معماری نباید به Redis وابسته باشد.

---

# 36. Queue Architecture

با توجه به محیط فعلی:

```text
Queue = sync
```

در فاز اول.

کارهای سنگین AI، تصویر یا Automation در صورت نیاز می‌توانند بعداً به Queue واقعی منتقل شوند.

Architecture نباید فرض کند Worker همیشه در دسترس است.

---

# 37. Security

اصول:

- Admin authentication
- Authorization
- CSRF protection
- Validation
- Mass-assignment protection
- Secure file upload
- MIME/type validation
- File size limits
- Secret encryption
- Rate limiting
- Audit trail for sensitive settings
- Never expose API keys to frontend

---

# 38. AI API Key

API Key:

```text
Admin
 ↓
Encrypted server-side storage
 ↓
AI Service
 ↓
Provider
```

هرگز:

```text
Browser
 ↓
API Key
 ↓
Provider
```

نباید رخ دهد.

---

# 39. Performance

هدف:

- Fast first load
- Server-rendered public pages
- Minimal JavaScript
- Optimized images
- Lazy loading
- Cache public content
- Avoid unnecessary API calls
- Avoid loading inactive modules
- Avoid N+1 queries

Homepage باید حتی با CMS غنی، سبک باقی بماند.

---

# 40. Extensibility

اضافه شدن Module جدید نباید به معنی بازنویسی معماری باشد.

مثلاً در آینده:

```text
Speaking
Newsletter
Community
Books
Events
Courses
```

می‌توانند اضافه شوند.

اما هر قابلیت جدید باید بعد از بررسی Domain Model اضافه شود، نه اینکه همه چیز به یک `Content` generic تبدیل شود.

---

# 41. Implementation Strategy

ترتیب پیشنهادی:

```text
1. Domain Model
2. Database Schema
3. Models & Relationships
4. Admin Authentication / Authorization
5. CMS Core
6. Content Modules
7. Public Rendering
8. Homepage Composition
9. SEO
10. Media
11. Search
12. AI Layer
13. Automation
14. Performance
15. Testing
16. Deployment
```

---

# 42. مهم‌ترین تصمیم معماری

این پروژه نباید به شکل:

```text
Page
 └── Hard-coded content
```

ساخته شود.

مدل صحیح:

```text
Domain Entity
      ↓
CMS
      ↓
Presentation Module
      ↓
Public Page
```

و Homepage:

```text
Content
+
Relationships
+
Current State
+
Module Configuration
      ↓
Homepage Composition
```

---

# 43. Architectural Target

هدف نهایی:

```text
                   HOSSEIN YARIAM
                         │
                  Domain Model
                         │
              ┌──────────┴──────────┐
              │                     │
           Admin CMS          Application Services
              │                     │
              └──────────┬──────────┘
                         │
                     Public Site
                         │
              ┌──────────┼──────────┐
              ↓          ↓          ↓
          Homepage    Content     Discovery
              │          │          │
              └──────────┴──────────┘
                         │
                  Living Profile
```

---

# 44. Next Stage

مرحله بعد باید وارد **Detailed Domain & Database Design** شود:

1. نهایی‌سازی Entityها
2. تعریف فیلدهای هر Entity
3. تعریف رابطه‌های One-to-One / One-to-Many / Many-to-Many
4. تصمیم درباره Polymorphic Relations
5. طراحی Translation model
6. طراحی Visibility / Publishing model
7. طراحی Module configuration
8. طراحی Media
9. طراحی SEO
10. طراحی AI settings و AI usage
11. ERD
12. Migration plan

بعد از تأیید مدل داده، ساختار دقیق Laravel و Admin CMS طراحی می‌شود.
