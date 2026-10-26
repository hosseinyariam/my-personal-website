<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * سرویس فرم تماس.
 *
 * ── ترتیب کار عمدی است ──
 * پیام **اول** در دیتابیس ذخیره می‌شود، **بعد** ایمیل فرستاده می‌شود.
 * دلیل: روی هاست اشتراکی SMTP می‌تواند قطع شود یا rate limit بخورد.
 * اگر ترتیب برعکس بود، پیام کاربر از دست می‌رفت و هیچ ردی نمی‌ماند.
 * حالا با `mail_sent=false` مدیر در پنل می‌بیند کدام پیام ارسال نشده.
 *
 * ── محافظت ضد اسپم ──
 *   ۱. Honeypot — فیلد پنهانی که فقط ربات پر می‌کند
 *   ۲. Throttle — ۵ ارسال در ساعت به‌ازای IP (در مسیر)
 *   ۳. بررسی زمان پرکردن فرم — ارسال زیر ۲ ثانیه یعنی ربات
 * ──────────────────────────
 */
class ContactService
{
    /** حداقل ثانیه برای پرکردن واقعی فرم. */
    private const MIN_FILL_SECONDS = 2;

    /**
     * ثبت یک پیام تماس.
     *
     * @param  array{name: string, email: string, purpose?: string|null, message: string}  $data
     * @return array{message: ContactMessage|null, spam: bool}
     */
    public function submit(array $data, string $ipHash, ?string $userAgent = null, bool $isSpam = false): array
    {
        $spam = $isSpam || $this->looksLikeSpam($data);

        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'purpose' => $data['purpose'] ?? null,
            'message' => $data['message'],
            'ip_hash' => $ipHash,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            'is_spam' => $spam,
        ]);

        // پیام اسپم ذخیره می‌شود ولی ایمیل نمی‌فرستد — اینطور
        // الگوی اسپم قابل بررسی می‌ماند بدون آلوده‌کردن صندوق ورودی.
        if (! $spam) {
            $this->sendNotification($message);
        }

        return ['message' => $message, 'spam' => $spam];
    }

    /**
     * ارسال اعلان به مدیر.
     *
     * شکست ارسال، درخواست کاربر را نمی‌شکند: پیام ذخیره شده و
     * فقط فلگ `mail_sent` روی false می‌ماند.
     */
    private function sendNotification(ContactMessage $message): void
    {
        $recipient = config('mail.contact_notify_email') ?: config('mail.from.address');

        if (blank($recipient)) {
            // بدون آدرس اعلان، ارسال معنا ندارد. پیام ذخیره شده است
            // و مدیر در پنل می‌بیند — این یک پیکربندی ناقص است، نه خطا.
            Log::info('پیام تماس ذخیره شد ولی آدرس اعلان تنظیم نشده است.', [
                'contact_message_id' => $message->getKey(),
            ]);

            return;
        }

        try {
            Mail::raw(
                $this->plainTextBody($message),
                function ($mail) use ($recipient, $message): void {
                    $mail->to($recipient)
                        ->subject('پیام جدید از فرم تماس — '.$message->name)
                        // پاسخ مستقیم به فرستنده، بدون اینکه ایمیل او
                        // به‌عنوان From جعل شود (جلوگیری از spoofing).
                        ->replyTo($message->email, $message->name);
                }
            );

            $message->forceFill(['mail_sent' => true])->save();
        } catch (\Throwable $e) {
            // خطا ثبت می‌شود ولی جزئیات حساس (رمز SMTP) لو نمی‌رود.
            Log::error('ارسال ایمیل پیام تماس شکست خورد.', [
                'contact_message_id' => $message->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function plainTextBody(ContactMessage $message): string
    {
        return implode("\n", [
            'پیام جدید از فرم تماس hosseinyariam.ir',
            '',
            'نام: '.$message->name,
            'ایمیل: '.$message->email,
            'موضوع: '.($message->purpose ?: '—'),
            'زمان: '.$message->created_at?->format('Y-m-d H:i'),
            '',
            'متن پیام:',
            $message->message,
        ]);
    }

    /**
     * نشانه‌های اسپم در محتوا.
     *
     * این جانشین honeypot و throttle است، نه مکمل آن. الگوها محافظه‌کارانه
     * انتخاب شده‌اند تا پیام واقعی رد نشود — ردکردن پیام واقعی هزینه‌ی
     * بیشتری از پذیرش یک اسپم دارد.
     *
     * @param  array<string, mixed>  $data
     */
    private function looksLikeSpam(array $data): bool
    {
        $text = mb_strtolower(($data['name'] ?? '').' '.($data['message'] ?? ''));

        $patterns = [
            '/(https?:\/\/[^\s]+){3,}/u',      // سه لینک یا بیشتر
            '/\[url=/i',                        // نشانه‌ی BBCode اسپم
            '/\b(casino|viagra|cialis|porn|xxx)\b/i',
            '/\b(سکس|قمار|شرط‌بندی|کازینو)\b/u',
            '/(bitcoin|crypto).{0,20}(investment|profit|double)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * آیا فرم به‌قدر کافی کُند پر شده که ربات نباشد؟
     *
     * ربات‌ها فرم را در چند میلی‌ثانیه پر و ارسال می‌کنند. این بررسی
     * در FormRequest استفاده می‌شود.
     */
    public function isHumanTiming(?int $formOpenedAt): bool
    {
        if ($formOpenedAt === null) {
            // اگر مهر زمانی نبود، کاربر را رد نمی‌کنیم — honeypot و
            // throttle همچنان فعالند. سخت‌گیری بی‌دلیل، کاربر واقعی
            // را می‌راند.
            return true;
        }

        return (time() - $formOpenedAt) >= self::MIN_FILL_SECONDS;
    }

    /**
     * هش IP برای throttle — بدون نگه‌داشتن IP خام.
     *
     * چرا هش و نه IP: برای rate limit فقط نیاز به تشخیص «همان
     * بازدیدکننده» است. هش با نمک برنامه این را می‌دهد بدون اینکه
     * داده‌ی شخصی قابل ردیابی ذخیره شود.
     */
    public function hashIp(?string $ip): ?string
    {
        if (blank($ip)) {
            return null;
        }

        return hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}
