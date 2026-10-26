<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * پروفایل — هویت عمومی صاحب سایت.
 *
 * تصمیم: این جدول با جدول `users` یکی نیست. IA:603 اصل «Biography ≠ Resume»
 * و معماری:56-60 اصل «جدایی موجودیت‌ها» را الزام می‌کنند. کاربر = حساب ورود؛
 * پروفایل = هویت عمومی. این جدایی باعث می‌شود تغییر رمز یا افزودن مدیر دوم
 * هرگز محتوای سایت را تحت تأثیر نگذارد.
 *
 * یکتایی «یک پروفایل فعال» در لایه‌ی اپلیکیشن و با تست اعمال می‌شود
 * (ERD:54-64 و Blueprint:161-164 هر دو همین را گفته‌اند).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();

            // ── هویت ──
            $table->string('display_name', 160);
            $table->string('professional_title', 200)->nullable();
            $table->string('short_bio', 500)->nullable();
            $table->longText('biography')->nullable();

            // ── روایت و موضع‌گیری ──
            $table->text('positioning_statement')->nullable();
            $table->text('philosophy')->nullable();
            $table->json('core_values')->nullable();

            // ── تمرکز و وضعیت ──
            $table->text('current_mission')->nullable();
            $table->string('availability_status', 40)->nullable();
            $table->string('location_label', 160)->nullable();

            // ── تماس عمومی (فقط چیزی که صریحاً عمومی است) ──
            $table->string('email_public', 190)->nullable();

            // ── رسانه‌ها ──
            $table->foreignId('avatar_media_id')->nullable();
            $table->foreignId('resume_media_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
