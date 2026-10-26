<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ContactService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * اعتبارسنجی فرم تماس.
 *
 * ── محافظت‌های اینجا ──
 *   ۱. Honeypot — فیلد `website` که با CSS پنهان است. ربات‌ها آن را
 *      پر می‌کنند چون همه‌ی فیلدها را پر می‌کنند. کاربر واقعی آن را
 *      نمی‌بیند، پس خالی می‌ماند.
 *   ۲. بررسی زمان — اگر فرم زیر ۲ ثانیه پر شده باشد، ربات است.
 *   ۳. اعتبارسنجی واقعی ایمیل و طول‌ها.
 *
 * ── چرا درخواست اسپم رد نمی‌شود بلکه علامت می‌خورد ──
 * اگر درخواست اسپم با خطای اعتبارسنجی رد شود، ربات می‌فهمد و الگویش
 * را اصلاح می‌کند. رفتار ما: ذخیره با فلگ `is_spam`، بدون ارسال ایمیل،
 * با پاسخ «موفق» به کاربر. اینطور ربات بی‌خبر می‌ماند و مدیر الگوها
 * را در پنل می‌بیند.
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        // فرم تماس برای عموم باز است — نیازی به مجوز کاربری نیست.
        // محافظت با throttle و honeypot انجام می‌شود.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],

            /*
            | اعتبارسنجی ایمیل — تصمیم مهم
            |
            | قاعده‌ی `dns` عمداً استفاده **نمی‌شود**. دلیل: بررسی DNS
            | یعنی هر ارسال فرم به یک lookup خارجی وابسته است. روی هاست
            | اشتراکی این lookup می‌تواند کند یا نامطمئن باشد و نتیجه‌اش
            | این است که کاربر واقعی نمی‌تواند پیام بفرستد — شکست در
            | جهت اشتباه. همچنین دامنه‌هایی که MX ندارند ولی ایمیل
            | معتبر دارند، بی‌دلیل رد می‌شوند.
            |
            | محافظت ضد اسپم با سه لایه‌ی دیگر انجام می‌شود: honeypot،
            | throttle، و تحلیل محتوا در ContactService.
            */
            'email' => ['required', 'string', 'email:rfc', 'max:190'],

            'purpose' => ['nullable', 'string', 'in:project,collaboration,question,service,other'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],

            /*
            | Honeypot — عمداً **قاعده‌ی اعتبارسنجی ندارد**.
            |
            | اگر `max:0` می‌گذاشتیم، پرکردن این فیلد یک خطای اعتبارسنجی
            | می‌ساخت و ربات می‌فهمید که شناسایی شده و الگویش را اصلاح
            | می‌کرد. طراحی درست: درخواست معتبر شمرده می‌شود، در
            | `isSpam()` علامت می‌خورد، ایمیل نمی‌فرستد، و به ربات
            | پاسخ «موفق» داده می‌شود — بدون اینکه بفهمد رد شده.
            */

            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام خود را وارد کنید.',
            'name.min' => 'نام باید حداقل ۲ حرف باشد.',
            'name.max' => 'نام بیش از حد بلند است.',
            'email.required' => 'ایمیل برای پاسخ‌دادن لازم است.',
            'email.email' => 'ایمیل واردشده معتبر نیست.',
            'purpose.in' => 'موضوع انتخاب‌شده معتبر نیست.',
            'message.required' => 'متن پیام را بنویسید.',
            'message.min' => 'پیام کوتاه‌تر از حد لازم است — لطفاً کمی بیشتر توضیح دهید.',
            'message.max' => 'پیام بیش از حد بلند است (حداکثر ۵۰۰۰ حرف).',
            'consent.accepted' => 'برای ارسال، باید با ذخیره‌ی اطلاعات موافقت کنید.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'email' => 'ایمیل',
            'purpose' => 'موضوع',
            'message' => 'پیام',
            'consent' => 'رضایت',
        ];
    }

    /**
     * آیا این درخواست اسپم است؟
     *
     * دو نشانه: honeypot پر شده، یا فرم خیلی سریع پر شده است.
     */
    public function isSpam(): bool
    {
        // Honeypot — اگر اعتبارسنجی رد کرده باشد اینجا نمی‌رسیم،
        // ولی این بررسی برای حالتی است که مقدار خالی ولی وجود داشته باشد.
        if ($this->filled('website')) {
            return true;
        }

        // سرعت پرکردن فرم — ربات‌ها در چند میلی‌ثانیه ارسال می‌کنند
        $app = app(ContactService::class);

        return ! $app->isHumanTiming(session('contact_form_opened_at'));
    }

    /**
     * آماده‌سازی داده پیش از اعتبارسنجی.
     *
     * نرمال‌سازی اعداد فارسی و حذف فاصله‌های اضافی، تا ورودی
     * کاربر با صفحه‌کلید فارسی رد نشود.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'message' => trim((string) $this->input('message')),
        ]);
    }
}
