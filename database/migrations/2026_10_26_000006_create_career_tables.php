<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سابقه‌ی کاری، تحصیلات، دستاورد، گواهی‌نامه.
 *
 * سیاست حذف کلیدی: `experiences.company_id` با `nullOnDelete` تعریف می‌شود
 * (ERD:389). حذف یک شرکت نباید سابقه‌ی کاری را پاک کند — آن سابقه بخشی از
 * مسیر حرفه‌ای است، حتی اگر شرکت دیگر روی سایت نباشد.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── سابقه‌ی کاری ──
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('title', 180);
            $table->string('employment_type', 40)->nullable();
            $table->string('location_label', 160)->nullable();
            $table->text('description')->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index(['started_at']);
        });

        // ── تحصیلات ──
        Schema::create('education', function (Blueprint $table): void {
            $table->id();
            $table->string('institution', 180);
            $table->string('degree', 160)->nullable();
            $table->string('field_of_study', 180)->nullable();
            $table->text('description')->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        // ── دستاورد ──
        // `evidence_url` اینجا اجباری نیست ولی بدون شاهد، دستاورد در
        // سایت عمومی نمایش داده نمی‌شود. اصل «Evidence Over Claims».
        Schema::create('achievements', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->date('achieved_at')->nullable();
            $table->string('evidence_url', 2048)->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index('is_featured');
        });

        // ── گواهی‌نامه ──
        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('issuer', 180);
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('credential_url', 2048)->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('education');
        Schema::dropIfExists('experiences');
    }
};
