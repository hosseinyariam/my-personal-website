<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مسیر حرفه‌ای، تمرکز فعلی، کانال‌های ارتباطی، پیام‌های تماس.
 *
 * TimelineEvent به‌صورت چندریختی تک‌هدف است (تصمیم D5): هر رویداد
 * دقیقاً به یک موجودیت اصلی وصل می‌شود. این از چهار کلید خارجی خالی
 * جلوگیری می‌کند و با «MVP: یک هدف اصلی» در Blueprint:429-431 هم‌خوان است.
 *
 * CurrentFocus سه کلید خارجی خالی دارد، اما اعتبارسنجی تضمین می‌کند
 * حداکثر یکی پر باشد (تصمیم D6 + تست اختصاصی Blueprint:874).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── رویداد مسیر حرفه‌ای ──
        Schema::create('timeline_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('summary')->nullable();
            $table->string('event_type', 40)->default('milestone');

            $table->date('occurred_at')->nullable();
            $table->date('ended_at')->nullable();

            // چندریختی تک‌هدف — نوع فقط از registry داخلی می‌آید
            $table->string('timelineable_type', 100)->nullable();
            $table->unsignedBigInteger('timelineable_id')->nullable();

            $table->string('status', 30)->default('draft');
            $table->string('visibility', 20)->default('private');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_highlight')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['timelineable_type', 'timelineable_id']);
            $table->index(['status', 'visibility', 'occurred_at']);
            $table->index(['is_highlight', 'sort_order']);
        });

        // ── تمرکز فعلی ──
        // IA:606 می‌گوید «Current Focus = Dynamic». این جدول همان
        // پویایی را ممکن می‌کند؛ بدون آن، متن ثابت در Blade می‌ماند.
        Schema::create('current_focuses', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('focus_type', 30)->default('building');
            $table->string('status', 30)->default('active');

            $table->date('started_at')->nullable();
            $table->date('target_date')->nullable();

            // حداکثر یکی از این سه پر می‌شود (اعتبارسنجی + تست)
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'is_featured', 'sort_order']);
            $table->index('focus_type');
        });

        // ── کانال‌های ارتباطی ──
        Schema::create('contact_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('label', 100);
            $table->string('channel_type', 40)->default('email');
            $table->string('url', 2048);
            $table->string('handle', 180)->nullable();
            $table->string('purpose', 40)->default('general');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'is_public', 'sort_order']);
        });

        // ── پیام‌های فرم تماس ──
        // پیام‌ها ذخیره می‌شوند حتی اگر ارسال ایمیل شکست بخورد؛ فلگ
        // `mail_sent` اجازه‌ی پیگیری دستی می‌دهد. `ip_hash` به‌جای IP خام
        // ذخیره می‌شود — برای throttle کافی است ولی داده‌ی شخصی کمتری نگه می‌دارد.
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('purpose', 40)->nullable();
            $table->text('message');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->boolean('mail_sent')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['is_read', 'created_at']);
            $table->index('ip_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('contact_channels');
        Schema::dropIfExists('current_focuses');
        Schema::dropIfExists('timeline_events');
    }
};
