<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تاکسونومی: دسته‌بندی، برچسب، مهارت، فناوری.
 *
 * این چهار جدول هیچ وابستگی به موجودیت‌های دیگر ندارند، پس اول ساخته می‌شوند.
 * Blueprint:339-363 و ERD:155-169.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── دسته‌بندی یادداشت‌ها ──
        // تصمیم D10: هفت حوزه‌ی IA:213-220 به‌عنوان دسته‌ی قابل مدیریت.
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        // ── برچسب ──
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        // ── مهارت ──
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('category', 80)->nullable();
            // برچسب سطح به‌صورت متن است، نه عدد؛ چون UI Kit:224 تصریح می‌کند
            // «سطح لازم نیست عددی نمایش داده شود» و ادعای عددی بی‌شاهد ممنوع است.
            $table->string('proficiency_label', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('category');
        });

        // ── فناوری ──
        Schema::create('technologies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('category', 80)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->string('icon_key', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
