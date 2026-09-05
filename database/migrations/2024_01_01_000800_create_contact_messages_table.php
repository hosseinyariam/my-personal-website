<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دیتابیس منبع حقیقتِ تماس است؛ ایمیل فقط اطلاع‌رسانی.
 * اگر ارسال ایمیل شکست بخورد، پیام با mail_sent=false در پنل دیده می‌شود (US-M5-3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150);
            $table->enum('purpose', ['job', 'service', 'other'])->default('other');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('mail_sent')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
