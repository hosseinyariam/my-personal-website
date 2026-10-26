<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ماژول‌ها، بخش‌های صفحه اصلی، تنظیمات، هوش مصنوعی.
 *
 * اصل معماری:488: هیچ نمای Blade نباید مستقیماً تصمیم بگیرد چه چیزی
 * نمایش داده شود. جدول `modules` منبع حقیقت است و middleware/public query
 * از آن می‌خواند. جدول `homepage_sections` فقط ترتیب و پیکربندی نگه
 * می‌دارد؛ محتوا در جدول‌های خودش می‌ماند (Blueprint:521-533).
 *
 * `settings` هرگز راز نگه نمی‌دارد (Blueprint:545-548)؛ کلید AI فقط در
 * `ai_providers` و با `encrypted` cast.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── ماژول‌ها ──
        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });

        // ── بخش‌های صفحه اصلی ──
        // تصمیم: `selection_mode` از ERD:331 نگه داشته شد (automatic | manual |
        // hybrid). این «انتخاب دستی ویژگی‌دار با بازگشت خودکار» را ممکن می‌کند
        // بدون ساختن یک صفحه‌ساز پیچیده که مستندات صریحاً منع کرده‌اند.
        Schema::create('homepage_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('title', 160)->nullable();
            $table->string('module_key', 80)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('selection_mode', 20)->default('automatic');
            $table->unsignedSmallInteger('display_limit')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
            $table->index('module_key');
        });

        // ── تنظیمات عمومی ──
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 160)->unique();
            $table->longText('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->string('group', 80)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('group');
        });

        // ── فراهم‌کننده‌های هوش مصنوعی ──
        // جدول ساخته می‌شود تا ساختار آماده باشد، ولی در MVP هیچ رکوردی
        // ندارد و `AI_ENABLED=false` است. Blueprint:775-777 فعال‌سازی را
        // مشروط به ورود AI به برنامه‌ی اجرایی کرده است.
        Schema::create('ai_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('provider_key', 80);
            $table->string('base_url', 2048)->nullable();
            $table->string('model_name', 160)->nullable();

            // با cast رمزنگاری‌شده ذخیره می‌شود؛ هرگز به Blade/JS/Log نمی‌رود
            $table->text('api_key')->nullable();

            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index('is_enabled');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('modules');
    }
};
