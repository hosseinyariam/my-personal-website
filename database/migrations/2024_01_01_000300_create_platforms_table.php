<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ویترین پلتفرم‌ها — قلب وعده‌ی G4.
 * فیلدهای اختیاری فراوان + status + is_featured + sort_order
 * یعنی «پلتفرم جدید = یک رکورد»، بدون کدنویسی صفحه‌ی جدید.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('title', 80);
            $table->string('tagline', 160)->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->json('features')->nullable();      // [{title, icon, description}]
            $table->json('tech_stack')->nullable();    // [string]
            $table->enum('status', ['live', 'beta', 'development', 'archived'])->default('development');
            $table->string('external_url', 255)->nullable();
            $table->string('repo_url', 255)->nullable();
            $table->string('version', 20)->nullable();
            $table->string('license', 40)->nullable();
            $table->string('icon', 40)->nullable();    // کلید آیکون کاشی
            $table->string('og_image', 255)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
