<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فراداده‌ی SEO — چندریختی با یگانگی ترکیبی.
 *
 * هر موجودیت حداکثر یک ردیف SEO دارد (`unique(seoable_type, seoable_id)`).
 * چون این رابطه چندریختی است، MySQL نمی‌تواند کلید خارجی واقعی بسازد؛
 * سلامت داده در سرویس و Observer تضمین می‌شود و ردیف یتیم هنگام حذف
 * موجودیت پاک می‌شود (معماری:305).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type', 100);
            $table->unsignedBigInteger('seoable_id');

            $table->string('meta_title', 220)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('robots', 80)->nullable();

            $table->string('og_title', 220)->nullable();
            $table->text('og_description')->nullable();
            $table->foreignId('og_image_media_id')->nullable()->constrained('media')->nullOnDelete();

            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
            $table->index(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
