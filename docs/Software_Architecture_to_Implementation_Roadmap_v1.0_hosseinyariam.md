# Software Architecture to Implementation Roadmap v1.0 --- hosseinyariam.ir

## هدف سند

این سند مراحل طراحی فنی سایت شخصی hosseinyariam.ir را از معماری نرم‌افزار
تا آماده‌سازی پیاده‌سازی تجمیع می‌کند.

هدف: ساخت یک Professional Identity Platform ماژولار، قابل توسعه و قابل
نگهداری.

------------------------------------------------------------------------

# 1. Software Architecture

## معماری انتخابی

Modular Monolith

Stack:

-   Backend: Laravel 12 + PHP 8.4
-   Database: MySQL
-   Frontend: Blade + Tailwind CSS + JavaScript + Vite
-   Infrastructure: cPanel / Apache / PHP-FPM

------------------------------------------------------------------------

# 2. Architecture Layers

Presentation ↓ Application ↓ Domain ↓ Infrastructure ↓ Persistence

------------------------------------------------------------------------

# 3. Domain Model

Entity های اصلی:

-   Profile
-   Experience
-   Education
-   Skill
-   Technology
-   Project
-   Product
-   Company
-   Note
-   TimelineEvent
-   CurrentFocus
-   ContactChannel
-   Module
-   Media
-   SEO
-   Setting
-   Category
-   Tag

------------------------------------------------------------------------

# 4. Entity Design

## Project

نماینده تجربه ساخت یا انجام یک کار.

فیلدهای اصلی:

-   title
-   slug
-   summary
-   description
-   problem
-   context
-   role
-   contribution
-   outcome
-   lessons_learned
-   status
-   visibility

ارتباط:

Project → Product\
Project → Company\
Project → Technology\
Project → Skill\
Project → Notes\
Project → Timeline

------------------------------------------------------------------------

## Product

محصول مستقل:

-   name
-   slug
-   description
-   problem
-   solution
-   current_state
-   status
-   visibility

ارتباط:

Product → Company\
Product → Projects\
Product → Notes

------------------------------------------------------------------------

## Company

-   name
-   description
-   website
-   relationship
-   timeline

------------------------------------------------------------------------

## Notes

محتوای فکری و تجربه:

-   title
-   slug
-   body
-   type
-   published_at
-   status

------------------------------------------------------------------------

## TimelineEvent

برای روایت مسیر حرفه‌ای:

-   title
-   description
-   date
-   type
-   visibility

------------------------------------------------------------------------

## CurrentFocus

برای نمایش وضعیت فعلی:

-   Building
-   Learning
-   Exploring
-   Goal

------------------------------------------------------------------------

# 5. Relationships

One To Many:

Company → Products

Product → Projects

Many To Many:

Project ↔ Skills

Project ↔ Technologies

Note ↔ Tags

Polymorphic:

-   Media
-   SEO
-   Timeline Relations

------------------------------------------------------------------------

# 6. Database Direction

جدول‌های اصلی:

users profiles companies products projects notes timeline_events
current_focuses experiences education skills technologies media modules
settings seo_metadata

------------------------------------------------------------------------

# 7. Laravel Architecture

ساختار پیشنهادی:

app/

Domain/

-   Profile
-   Project
-   Product
-   Company
-   Note
-   Timeline

Application/

-   Services
-   Actions

Infrastructure/

-   AI
-   Search
-   Storage

Http/

-   Controllers
-   Requests

------------------------------------------------------------------------

# 8. CMS Architecture

بخش‌ها:

Dashboard

Content: - Projects - Products - Companies - Notes - Experiences

Identity: - Profile - Biography

Presentation: - Homepage - Modules - Navigation

Media

SEO

AI

Settings

------------------------------------------------------------------------

# 9. Module System

هر بخش قابلیت فعال و غیرفعال شدن دارد.

نمونه:

-   projects
-   products
-   notes
-   timeline
-   current_focus

------------------------------------------------------------------------

# 10. Homepage Architecture

Homepage یک Living Profile است:

-   Hero
-   Current Focus
-   Featured Projects
-   Featured Products
-   Notes
-   Timeline
-   Biography Preview
-   Contact

------------------------------------------------------------------------

# 11. AI Architecture

CMS

↓

AI Service

↓

Provider Adapter

↓

AI Provider

تنظیمات:

-   Provider
-   Model
-   Base URL
-   API Key

API Key فقط در Backend ذخیره می‌شود.

------------------------------------------------------------------------

# 12. SEO Architecture

SeoMetadata:

-   title
-   description
-   canonical
-   open graph
-   robots

------------------------------------------------------------------------

# 13. Media Architecture

Media Entity:

-   file
-   type
-   alt
-   caption
-   metadata

------------------------------------------------------------------------

# 14. Localization

معماری:

Persian First / English Ready

------------------------------------------------------------------------

# 15. Implementation Roadmap

Phase 1: - Domain Model - Database - Authentication

Phase 2: - CMS Core - Admin Panel

Phase 3: - Content Modules

Phase 4: - Public Website

Phase 5: - AI - Automation - Search - Optimization

------------------------------------------------------------------------

# 16. اصول معماری

1.  Project ≠ Product ≠ Company
2.  Domain First
3.  Evidence Over Claims
4.  Homepage = Living Profile
5.  CMS Rich / Public Clean
6.  Modular Architecture
7.  AI as Service Layer
8.  Persian First / English Ready

------------------------------------------------------------------------

# مرحله بعد

Detailed ERD + Database Schema Design
