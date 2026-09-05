<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رزومه در چهار جدول مستقل — هر بخش جداگانه از پنل مدیریت می‌شود
 * و بخشِ خالی در صفحه‌ی عمومی مخفی می‌ماند (پیوست ب).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company', 100);
            $table->string('role', 100);
            $table->date('started_at');
            $table->date('ended_at')->nullable();     // NULL = تا اکنون
            $table->text('achievements')->nullable(); // مارک‌داون
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('resume_educations', function (Blueprint $table) {
            $table->id();
            $table->string('institution', 100);
            $table->string('degree', 100);
            $table->string('field', 100)->nullable();
            $table->smallInteger('started_year');
            $table->smallInteger('ended_year')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('resume_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->enum('category', ['product', 'design', 'technical']);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order']);
        });

        Schema::create('resume_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('issuer', 100);
            $table->smallInteger('year');
            $table->string('url', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_certificates');
        Schema::dropIfExists('resume_skills');
        Schema::dropIfExists('resume_educations');
        Schema::dropIfExists('resume_experiences');
    }
};
