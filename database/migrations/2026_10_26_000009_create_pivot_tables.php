<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول‌های پیوندی (pivot) — همه‌ی روابط چند‌به‌چند.
 *
 * قاعده‌ی کلیدی حذف (ERD:126): فقط ردیف pivot با حذف هر طرف cascade می‌شود،
 * ولی خودِ موجودیت‌های شرکت/محصول/پروژه/یادداشت هرگز به هم cascade نمی‌کنند.
 * یعنی حذف یک محصول، پروژه‌های مرتبطش را پاک نمی‌کند — فقط پیوند را.
 *
 * هر pivot یک قید یگانگی ترکیبی دارد تا پیوند تکراری ممکن نباشد.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── شرکت ↔ محصول ──
        Schema::create('company_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('relationship_type', 40)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id']);
        });

        // ── شرکت ↔ پروژه ──
        Schema::create('company_project', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('relationship_type', 40)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'project_id']);
        });

        // ── محصول ↔ پروژه (تصمیم D4: چند‌به‌چند، نه یک‌به‌چند) ──
        // یک پروژه می‌تواند زیربنای چند محصول باشد و یک محصول از چند
        // پروژه ساخته شود. Roadmap گفته بود یک‌به‌چند، اما Blueprint:268-270
        // و ERD:122-124 هر دو چند‌به‌چند را تعریف کرده‌اند.
        Schema::create('product_project', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('relationship_type', 40)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'project_id']);
        });

        // ── پروژه ↔ مهارت ──
        Schema::create('project_skill', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });

        // ── پروژه ↔ فناوری ──
        Schema::create('project_technology', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained('technologies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'technology_id']);
        });

        // ── یادداشت ↔ برچسب ──
        Schema::create('note_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'tag_id']);
        });

        // ── یادداشت ↔ دسته ──
        Schema::create('note_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'category_id']);
        });

        // ── یادداشت ↔ پروژه / محصول / شرکت ──
        // این پیوندها حلقه‌ی «کشف» (IA:341-355) را می‌سازند: کاربر از یک
        // یادداشت به پروژه‌ی مرتبط و از آن به محصول می‌رسد.
        Schema::create('note_project', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'project_id']);
        });

        Schema::create('note_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'product_id']);
        });

        Schema::create('note_company', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_company');
        Schema::dropIfExists('note_product');
        Schema::dropIfExists('note_project');
        Schema::dropIfExists('note_category');
        Schema::dropIfExists('note_tag');
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('product_project');
        Schema::dropIfExists('company_project');
        Schema::dropIfExists('company_product');
    }
};
