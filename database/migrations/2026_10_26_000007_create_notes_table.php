<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * یادداشت‌ها و مقالات.
 *
 * `reading_time_minutes` عمداً ذخیره نمی‌شود و در لحظه محاسبه می‌شود:
 * CMS:150-160 می‌گوید زمان مطالعه «محاسبه‌شده» است نه «ذخیره‌شده».
 * ذخیره‌کردن آن یعنی با هر ویرایش متن، عدد می‌تواند کهنه بماند.
 *
 * `published_at` می‌تواند در آینده باشد؛ انتشار واقعی با زمان‌بند انجام
 * می‌شود و Query عمومی آن را تا رسیدن زمان نشان نمی‌دهد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 220);
            $table->string('slug', 220)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');

            $table->string('type', 40)->default('note');
            $table->string('status', 30)->default('draft');
            $table->string('visibility', 20)->default('private');
            $table->timestamp('published_at')->nullable();

            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility', 'published_at']);
            $table->index(['is_featured', 'published_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
