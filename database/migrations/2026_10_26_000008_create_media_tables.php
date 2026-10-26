<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رسانه و پیوست‌های چندریختی.
 *
 * قاعده‌ی امنیتی معماری:275: محتوای باینری هرگز در MySQL ذخیره نمی‌شود —
 * فقط مسیر و فراداده. فایل روی دیسک می‌ماند.
 *
 * `media_attachments` چندریختی است ولی فقط از انواع داخلی و مورداعتماد
 * استفاده می‌کند (معماری:289)؛ نوع از سمت کلاینت پذیرفته نمی‌شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('disk', 40)->default('public');
            $table->string('path', 1024);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('caption')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('mime_type');
            $table->index('created_at');
        });

        Schema::create('media_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('mediable_type', 100);
            $table->unsignedBigInteger('mediable_id');
            $table->string('role', 60)->default('default');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
            $table->index(['mediable_type', 'mediable_id', 'role']);
        });

        // اکنون که `media` موجود است، کلیدهای خارجی پروفایل را وصل می‌کنیم.
        // `nullOnDelete` تا حذف تصویر، پروفایل را از بین نبرد.
        Schema::table('profiles', function (Blueprint $table): void {
            $table->foreign('avatar_media_id')->references('id')->on('media')->nullOnDelete();
            $table->foreign('resume_media_id')->references('id')->on('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropForeign(['avatar_media_id']);
            $table->dropForeign(['resume_media_id']);
        });

        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media');
    }
};
