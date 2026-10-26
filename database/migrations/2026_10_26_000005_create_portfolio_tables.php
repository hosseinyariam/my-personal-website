<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * شرکت، محصول، پروژه — سه موجودیت جدا.
 *
 * اصل قطعی IA:602 و معماری:56-60: Project ≠ Product ≠ Company.
 * هیچ‌کدام اینجا به هم اشاره نمی‌کنند؛ روابط در جدول‌های pivot جداگانه
 * می‌آید (migration 000009) تا حذف یکی، دیگری را پاک نکند.
 *
 * تصمیم D1: `status` + `visibility` (Blueprint) بر `publication_status` (ERD).
 * تصمیم D2: `started_at`/`completed_at` بر `start_date`/`end_date`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── شرکت ──
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('website_url', 2048)->nullable();

            // رابطه با حسین — مؤسس، کارمند، همکار، کارفرما، شریک
            $table->string('relationship_type', 40)->nullable();
            // نوع شرکت (استارتاپ، آژانس، …) — از ERD:75 نگه داشته شد
            $table->string('company_type', 40)->nullable();

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'sort_order']);
        });

        // ── محصول ──
        // محصول تنها موجودیتی است که «عرضه به بازار» را دنبال می‌کند،
        // پس چرخه‌ی وضعیتش با پروژه فرق دارد.
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->string('tagline', 220)->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('problem')->nullable();
            $table->longText('solution')->nullable();
            $table->longText('current_state')->nullable();

            $table->string('website_url', 2048)->nullable();
            $table->string('repository_url', 2048)->nullable();

            $table->string('status', 30)->default('idea');
            $table->date('started_at')->nullable();
            $table->date('launched_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'sort_order']);
        });

        // ── پروژه ──
        // ساختار کامل پرونده‌ی پروژه طبق IA:129-142: مسئله، زمینه، نقش،
        // مشارکت، نتیجه، آموخته‌ها. این فیلدها تزئینی نیستند؛ هرکدام
        // یکی از حلقه‌های «مسئله → ساخت → یادگیری» را مستند می‌کند.
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('problem')->nullable();
            $table->longText('context')->nullable();
            $table->string('role', 180)->nullable();
            $table->longText('contribution')->nullable();
            $table->longText('outcome')->nullable();
            $table->longText('lessons_learned')->nullable();

            $table->string('status', 30)->default('idea');
            $table->string('visibility', 20)->default('private');

            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();

            $table->string('repository_url', 2048)->nullable();
            $table->string('live_url', 2048)->nullable();

            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility', 'published_at']);
            $table->index(['is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('products');
        Schema::dropIfExists('companies');
    }
};
