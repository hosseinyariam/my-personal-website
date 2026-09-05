<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ثبت و اطلاع‌رسانی تماس.
 *
 * ترتیب عمداً «اول دیتابیس، بعد ایمیل» است: دیتابیس منبع حقیقت است و
 * شکست SMTP نباید پیام کاربر را از بین ببرد. پیامِ ارسال‌نشده با
 * mail_sent=false در پنل با نشان هشدار دیده می‌شود (US-M5-3 · ریسک ایمیل).
 */
final class ContactService
{
    public function __construct(private readonly Settings $settings) {}

    /** @param array{name: string, email: string, purpose: string, message: string} $data */
    public function record(array $data, ?string $ip = null, ?string $userAgent = null): ContactMessage
    {
        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'purpose' => $data['purpose'],
            'message' => $data['message'],
            'ip' => $ip,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            'mail_sent' => false,
        ]);

        $this->notify($message);

        return $message;
    }

    /** ارسال sync — بدون worker روی هاست اشتراکی (D06). خطا بلعیده و لاگ می‌شود. */
    private function notify(ContactMessage $message): void
    {
        $recipient = $this->settings->notificationEmail();

        if ($recipient === null) {
            Log::warning('ایمیل اعلان تنظیم نشده؛ پیام تماس فقط در دیتابیس ثبت شد.', [
                'contact_message_id' => $message->id,
            ]);

            return;
        }

        try {
            Mail::to($recipient)->send(new ContactMessageMail($message));
            $message->update(['mail_sent' => true]);
        } catch (\Throwable $e) {
            // کاربر نباید خطای SMTP را ببیند — پیامش با موفقیت ثبت شده است.
            Log::error('ارسال ایمیل اعلان تماس شکست خورد.', [
                'contact_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
